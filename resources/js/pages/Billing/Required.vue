<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import moment from 'moment'
import 'moment/locale/pt-br'
import {
  ArrowRight,
  Barcode,
  Check,
  CreditCard,
  Copy,
  LoaderCircle,
  QrCode,
  ShieldCheck,
  WalletCards,
} from 'lucide-vue-next'
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue'

declare global {
  interface Window { MercadoPago: any }
}

moment.locale('pt-br')

type BillingPeriod = 'monthly' | 'annual'

type PlanCatalogItem = {
  id: number
  code: 'basic' | 'medium' | 'plus' | 'premium'
  name: string
  monthly: number
  annual_total: number
  annual_monthly_equivalent: number
  limits: {
    products: number
    product_images: number | null
    gallery_images: number
    banners: number
  }
  trial_days: number
}

const props = defineProps<{
  is_blocked: boolean
  subscription: {
    id: number
    status: string
    installments: number
    installment_amount: number
    status_detail: string | null
    billing_period: BillingPeriod
    trial_ends_at: string | null
    trial_days_left: number | null
    next_billing_at: string | null
    price: number
    plan_code: PlanCatalogItem['code']
    plan_name: string
  }
  current_plan_limits: {
    products: number
    product_images: number | null
    gallery_images: number
    banners: number
  }
  plan_catalog: PlanCatalogItem[]
  payment_gateway: {
    provider: string
    supports_pix: boolean
    supports_credit_card: boolean
    supports_boleto: boolean
    configured: boolean
    public_key: string | null
    max_installments: number
  }
  pix_payment: {
    id: number
    qr_code: string | null
    qr_code_base64: string | null
    expires_at: string | null
  } | null
  payments: Array<{
    id: number
    amount: number
    currency: string
    method: string
    status: string
    created_at: string
    paid_at: string | null
  }>
}>()

const selectedPlanCode = ref<PlanCatalogItem['code']>(props.subscription.plan_code)
const selectedPeriod = ref<BillingPeriod>(props.subscription.billing_period)

const selectedPlan = computed(() => {
  return props.plan_catalog.find((plan) => plan.code === selectedPlanCode.value) ?? props.plan_catalog[0]
})
const hasPendingPlanChange = computed(() =>
  selectedPlanCode.value !== props.subscription.plan_code
  || selectedPeriod.value !== props.subscription.billing_period,
)

const breadcrumbs = computed(() => {
  if (props.is_blocked) {
    return [{ title: 'Assinatura', href: route('painel.billing.required') }]
  }

  return [
    { title: 'Painel', href: route('painel.index') },
    { title: 'Assinatura e cobrança', href: route('painel.billing.index') },
  ]
})

const selectedPrice = computed(() => {
  if (!selectedPlan.value) return 0
  return selectedPeriod.value === 'annual'
    ? selectedPlan.value.annual_total
    : selectedPlan.value.monthly
})

const planForm = useForm({
  plan_code: selectedPlanCode.value,
  billing_period: selectedPeriod.value,
})

const payForm = useForm({
  method: 'pix',
  token: '',
  payment_method_id: '',
  issuer_id: '',
  installments: 1,
  payer_email: '',
  identification_type: '',
  identification_number: '',
  checkout_attempt_id: crypto.randomUUID(),
})
const cardReady = ref(false)
const cardError = ref('')
const cardFormInstance = ref<any>(null)
let installmentObserver: MutationObserver | null = null

const paymentMethods = computed(() => [
  {
    value: 'pix', label: 'PIX', icon: QrCode,
    description: 'Aprovação rápida e pagamento por QR Code.', detail: 'Liberação imediata',
    enabled: props.payment_gateway.supports_pix,
  },
  {
    value: 'credit_card', label: 'Cartão de crédito', icon: CreditCard,
    description: 'Pague com segurança usando seu cartão.', detail: 'Processamento seguro',
    enabled: props.payment_gateway.supports_credit_card,
  },
  {
    value: 'boleto', label: 'Boleto bancário', icon: Barcode,
    description: 'Pague pelo banco de sua preferência.', detail: 'Compensação bancária', enabled: props.payment_gateway.supports_boleto,
  },
].filter((method) => method.enabled))

const selectedPaymentMethod = computed(() => paymentMethods.value.find((method) => method.value === payForm.method))
const pixCopied = ref(false)

if (!selectedPaymentMethod.value && paymentMethods.value[0]) {
  payForm.method = paymentMethods.value[0].value
}

const deleteForm = useForm({
  password: '',
})

function applyPlanSelection() {
  planForm.plan_code = selectedPlanCode.value
  planForm.billing_period = selectedPeriod.value
  planForm.post(route('painel.billing.plan'))
}

function payNow() {
  if (!hasPendingPlanChange.value && payForm.method === 'pix') payForm.post(route('painel.billing.pay'))
}

async function loadMercadoPagoSdk() {
  if (window.MercadoPago) return
  await new Promise<void>((resolve, reject) => {
    const script = document.createElement('script')
    script.src = 'https://sdk.mercadopago.com/js/v2'
    script.onload = () => resolve()
    script.onerror = () => reject(new Error('Não foi possível carregar o checkout seguro.'))
    document.head.appendChild(script)
  })
}

function restrictInstallments() {
  const select = document.querySelector<HTMLSelectElement>('#form-checkout__installments')
  if (!select) return
  const maximum = props.subscription.billing_period === 'monthly'
    ? 1
    : props.payment_gateway.max_installments
  Array.from(select.options).forEach((option) => {
    if (Number(option.value) > maximum) option.remove()
  })
  if (props.subscription.billing_period === 'monthly' && select.options.length) {
    select.options[0].text = `À vista de ${formatCurrency(props.subscription.price)}`
    select.value = '1'
  }
}

async function mountCardForm() {
  if (cardFormInstance.value || !props.payment_gateway.public_key) return
  cardError.value = ''
  await nextTick()
  try {
    await loadMercadoPagoSdk()
    const mp = new window.MercadoPago(props.payment_gateway.public_key, { locale: 'pt-BR' })
    cardFormInstance.value = mp.cardForm({
      amount: String(props.subscription.price),
      iframe: true,
      form: {
        id: 'form-checkout',
        cardNumber: { id: 'form-checkout__cardNumber', placeholder: 'Número do cartão' },
        expirationDate: { id: 'form-checkout__expirationDate', placeholder: 'MM/AA' },
        securityCode: { id: 'form-checkout__securityCode', placeholder: 'CVV' },
        cardholderName: { id: 'form-checkout__cardholderName', placeholder: 'Nome impresso no cartão' },
        issuer: { id: 'form-checkout__issuer', placeholder: 'Banco emissor' },
        installments: { id: 'form-checkout__installments', placeholder: 'Parcelas' },
        identificationType: { id: 'form-checkout__identificationType', placeholder: 'Documento' },
        identificationNumber: { id: 'form-checkout__identificationNumber', placeholder: 'Número do documento' },
        cardholderEmail: { id: 'form-checkout__cardholderEmail', placeholder: 'E-mail' },
      },
      callbacks: {
        onFormMounted(error: any) {
          if (error) { cardError.value = 'Não foi possível iniciar o formulário do cartão.'; return }
          cardReady.value = true
          const select = document.querySelector('#form-checkout__installments')
          if (select) {
            installmentObserver = new MutationObserver(restrictInstallments)
            installmentObserver.observe(select, { childList: true })
          }
        },
        onSubmit(event: Event) {
          event.preventDefault()
          const data = cardFormInstance.value.getCardFormData()
          payForm.token = data.token
          payForm.payment_method_id = data.paymentMethodId
          payForm.issuer_id = data.issuerId
          payForm.installments = Number(data.installments)
          payForm.payer_email = data.cardholderEmail
          payForm.identification_type = data.identificationType
          payForm.identification_number = data.identificationNumber
          payForm.post(route('painel.billing.pay'), { preserveScroll: true })
        },
        onFetching() {},
      },
    })
  } catch (error) {
    cardError.value = error instanceof Error ? error.message : 'Não foi possível iniciar o cartão.'
  }
}

watch(() => payForm.method, (method) => { if (method === 'credit_card') mountCardForm() })
onBeforeUnmount(() => { installmentObserver?.disconnect(); cardFormInstance.value?.unmount?.() })

async function copyPixCode() {
  if (!props.pix_payment?.qr_code) return
  await navigator.clipboard.writeText(props.pix_payment.qr_code)
  pixCopied.value = true
  window.setTimeout(() => { pixCopied.value = false }, 2500)
}

function deleteAccount() {
  deleteForm.delete(route('painel.billing.destroy-account'))
}

function formatCurrency(value: number): string {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(Number(value))
}

function formatNumber(value: number, decimals = 0): string {
  return new Intl.NumberFormat('pt-BR', {
    minimumFractionDigits: decimals,
    maximumFractionDigits: decimals,
  }).format(Number(value))
}

function formatDate(value: string | null): string | null {
  if (!value) return null
  return moment(value).format('DD/MM/YYYY')
}

function formatDateTime(value: string | null): string | null {
  if (!value) return null
  return moment(value).format('DD/MM/YYYY [às] HH:mm')
}

function trialDaysLeftLabel(value: number | null): string {
  if (value === null) return '-'
  const days = Math.trunc(value)
  if (days <= 0) return '0 dias'
  return `${formatNumber(days)} dia${days === 1 ? '' : 's'}`
}

function methodLabel(method: string) {
  if (method === 'credit_card') return 'Cartão'
  if (method === 'pix') return 'PIX'
  if (method === 'boleto') return 'Boleto'
  return method
}

function statusLabel(status: string) {
  if (status === 'paid') return 'Pago'
  if (status === 'pending') return 'Pendente'
  if (status === 'failed') return 'Falhou'
  if (status === 'refunded') return 'Estornado'
  if (status === 'trial') return 'Em trial'
  if (status === 'active') return 'Ativa'
  if (status === 'past_due') return 'Em atraso'
  if (status === 'expired') return 'Expirada'
  return status
}
</script>

<template>
  <Head title="Assinatura e cobrança" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mx-auto max-w-7xl p-4 md:p-6 space-y-6">
      <section
        class="rounded-3xl border p-6 md:p-8"
        :class="is_blocked ? 'border-amber-300 bg-gradient-to-br from-amber-50 to-white' : 'border-emerald-300 bg-gradient-to-br from-emerald-50 to-white'"
      >
        <h1 class="text-2xl md:text-3xl font-bold text-slate-900">
          {{ is_blocked ? 'Regularize sua assinatura' : 'Assinatura e cobrança' }}
        </h1>
        <p class="text-sm md:text-base mt-2 text-slate-700">
          {{ is_blocked
            ? 'Seu painel e sua vitrine pública estão bloqueados até a confirmação do pagamento.'
            : 'Acompanhe seu plano, altere o modelo de cobrança e visualize seu histórico com clareza.' }}
        </p>
      </section>

      <section class="grid xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 space-y-6">
          <div class="rounded-2xl border bg-white p-5 md:p-6">
            <h2 class="text-lg font-semibold text-slate-900">Resumo atual</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-4 text-sm">
              <div>
                <p class="text-slate-500">Status</p>
                <p class="font-semibold text-slate-900">{{ statusLabel(subscription.status) }}</p>
              </div>
              <div>
                <p class="text-slate-500">Plano</p>
                <p class="font-semibold text-slate-900">{{ subscription.plan_name }}</p>
              </div>
              <div>
                <p class="text-slate-500">Cobrança atual</p>
                <p class="font-semibold text-slate-900">{{ subscription.billing_period === 'annual' ? 'Anual' : 'Mensal' }}</p>
              </div>
              <div>
                <p class="text-slate-500">Valor vigente</p>
                <p class="font-semibold text-slate-900">{{ formatCurrency(subscription.price) }}/{{ subscription.billing_period === 'annual' ? 'ano' : 'mês' }}</p>
              </div>
              <div>
                <p class="text-slate-500">Fim do trial</p>
                <p class="font-semibold text-slate-900">{{ formatDate(subscription.trial_ends_at) ?? '-' }}</p>
              </div>
              <div>
                <p class="text-slate-500">Dias restantes de trial</p>
                <p class="font-semibold text-slate-900">{{ trialDaysLeftLabel(subscription.trial_days_left) }}</p>
              </div>
              <div>
                <p class="text-slate-500">Próxima cobrança</p>
                <p class="font-semibold text-slate-900">{{ formatDate(subscription.next_billing_at) ?? '-' }}</p>
              </div>
            </div>
          </div>

          <div class="rounded-2xl border bg-white p-5 md:p-6 space-y-4">
            <h2 class="text-lg font-semibold text-slate-900">Alterar assinatura</h2>

            <div class="space-y-2">
              <p class="text-sm font-medium text-slate-700">1. Escolha o plano</p>
              <div class="grid md:grid-cols-2 gap-3">
                <button
                  v-for="plan in plan_catalog"
                  :key="plan.id"
                  class="rounded-xl border p-4 text-left transition"
                  :class="selectedPlanCode === plan.code ? 'border-emerald-500 bg-emerald-50 shadow-sm' : 'hover:border-slate-300'"
                  @click="selectedPlanCode = plan.code"
                >
                  <p class="font-semibold text-slate-900">{{ plan.name }}</p>
                  <p class="text-sm text-slate-600 mt-1">Até {{ formatNumber(plan.limits.products) }} produtos</p>
                  <p class="text-sm text-slate-600">
                    {{ formatCurrency(plan.monthly) }}/mês
                  </p>
                </button>
              </div>
            </div>

            <div class="space-y-2">
              <p class="text-sm font-medium text-slate-700">2. Escolha o modelo de cobrança</p>
              <div class="grid md:grid-cols-2 gap-3">
                <button
                  class="rounded-xl border p-4 text-left transition"
                  :class="selectedPeriod === 'monthly' ? 'border-emerald-500 bg-emerald-50 shadow-sm' : 'hover:border-slate-300'"
                  @click="selectedPeriod = 'monthly'"
                >
                  <p class="font-semibold text-slate-900">Mensal</p>
                  <p class="text-sm text-slate-600 mt-1">Pagamento recorrente mensal</p>
                  <p class="text-base font-semibold text-slate-900 mt-2">
                    {{ selectedPlan ? formatCurrency(selectedPlan.monthly) : formatCurrency(0) }}/mês
                  </p>
                </button>

                <button
                  class="rounded-xl border p-4 text-left transition"
                  :class="selectedPeriod === 'annual' ? 'border-emerald-500 bg-emerald-50 shadow-sm' : 'hover:border-slate-300'"
                  @click="selectedPeriod = 'annual'"
                >
                  <p class="font-semibold text-slate-900">Anual</p>
                  <p class="text-sm text-slate-600 mt-1">Melhor custo por mês no ciclo anual</p>
                  <p class="text-base font-semibold text-slate-900 mt-2">
                    {{ selectedPlan ? formatCurrency(selectedPlan.annual_monthly_equivalent) : formatCurrency(0) }}/mês
                  </p>
                  <p class="text-xs text-slate-500 mt-1">
                    Total anual: {{ selectedPlan ? formatCurrency(selectedPlan.annual_total) : formatCurrency(0) }}
                  </p>
                </button>
              </div>
            </div>

            <div class="rounded-xl bg-slate-50 border p-4">
              <p class="text-sm text-slate-600">Resumo da alteração</p>
              <p class="font-semibold text-slate-900 mt-1">
                {{ selectedPlan?.name ?? '-' }} - {{ selectedPeriod === 'annual' ? 'Anual' : 'Mensal' }}
              </p>
              <p class="text-sm text-slate-700">Total do ciclo: {{ formatCurrency(selectedPrice) }}</p>
            </div>

            <button
              class="rounded-lg bg-slate-900 text-white py-2.5 px-4 disabled:opacity-50"
              :disabled="planForm.processing"
              @click="applyPlanSelection"
            >
              Salvar alteração de plano
            </button>
          </div>

          <div class="rounded-2xl border bg-white p-5 md:p-6">
            <h2 class="text-lg font-semibold text-slate-900">Limites do plano atual</h2>
            <div class="grid sm:grid-cols-2 gap-3 mt-4 text-sm">
              <p>Produtos: <b>{{ formatNumber(current_plan_limits.products) }}</b></p>
              <p>Fotos por produto: <b>{{ current_plan_limits.product_images === null ? 'Ilimitadas' : formatNumber(current_plan_limits.product_images) }}</b></p>
              <p>Fotos na galeria: <b>{{ formatNumber(current_plan_limits.gallery_images) }}</b></p>
              <p>Banners: <b>{{ formatNumber(current_plan_limits.banners) }}</b></p>
            </div>
          </div>

          <div class="rounded-2xl border bg-white p-5 md:p-6 space-y-3">
            <h2 class="text-lg font-semibold text-slate-900">Histórico de pagamentos</h2>
            <div v-if="!payments.length" class="text-sm text-slate-500">Nenhum pagamento registrado.</div>
            <div v-else class="space-y-2">
              <div
                v-for="payment in payments"
                :key="payment.id"
                class="rounded-lg border bg-slate-50 px-4 py-3 text-sm grid md:grid-cols-4 gap-2"
              >
                <p class="font-semibold text-slate-900">#{{ formatNumber(payment.id) }}</p>
                <p class="text-slate-700">{{ methodLabel(payment.method) }} - {{ statusLabel(payment.status) }}<span v-if="payment.installments > 1" class="block text-xs text-slate-500">{{ payment.installments }}x de {{ formatCurrency(payment.installment_amount) }}</span></p>
                <p class="text-slate-700">{{ formatDateTime(payment.paid_at ?? payment.created_at) }}</p>
                <p class="font-semibold text-slate-900 md:text-right">{{ formatCurrency(payment.amount) }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="space-y-6">
          <div class="overflow-hidden rounded-2xl border border-border bg-card text-card-foreground shadow-sm">
            <header class="border-b border-border bg-muted/40 p-5 md:p-6">
              <div class="flex items-start gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                  <WalletCards class="h-5 w-5" />
                </div>
                <div class="min-w-0 flex-1">
                  <h2 class="text-lg font-semibold text-foreground">Efetuar pagamento</h2>
                  <p class="mt-1 text-sm text-muted-foreground">Escolha como deseja pagar sua assinatura.</p>
                </div>
              </div>

              <div class="mt-4 flex items-center gap-2 rounded-lg border border-border bg-background px-3 py-2 text-xs text-muted-foreground">
                <ShieldCheck class="h-4 w-4 shrink-0 text-emerald-600 dark:text-emerald-400" />
                <span>Pagamento seguro por <b class="text-foreground">{{ payment_gateway.provider }}</b></span>
              </div>
            </header>

            <div class="space-y-5 p-5 md:p-6">
              <div v-if="pix_payment?.qr_code" class="rounded-xl border border-emerald-300 bg-emerald-50 p-4 text-center dark:border-emerald-800 dark:bg-emerald-950/30">
                <p class="font-semibold text-foreground">PIX aguardando pagamento</p>
                <p class="mt-1 text-xs text-muted-foreground">Leia o QR Code no aplicativo do seu banco ou copie o código abaixo.</p>
                <img v-if="pix_payment.qr_code_base64" :src="`data:image/png;base64,${pix_payment.qr_code_base64}`" alt="QR Code PIX" class="mx-auto mt-4 h-52 w-52 rounded-xl bg-white p-2" />
                <div class="mt-4 flex items-stretch gap-2">
                  <textarea readonly rows="3" :value="pix_payment.qr_code" class="min-w-0 flex-1 resize-none rounded-lg border border-border bg-background p-3 text-xs text-foreground"></textarea>
                  <button type="button" class="flex w-24 shrink-0 flex-col items-center justify-center gap-1 rounded-lg bg-emerald-600 px-3 text-xs font-semibold text-white hover:bg-emerald-700" @click="copyPixCode">
                    <Check v-if="pixCopied" class="h-4 w-4" /><Copy v-else class="h-4 w-4" />
                    {{ pixCopied ? 'Copiado' : 'Copiar PIX' }}
                  </button>
                </div>
                <p class="mt-3 text-xs text-muted-foreground">A confirmação acontece automaticamente após o pagamento.</p>
              </div>

              <div v-if="!payment_gateway.configured" class="rounded-xl border border-amber-300 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-950/30 dark:text-amber-200">
                O pagamento está temporariamente indisponível. A integração ainda não foi configurada.
              </div>
              <div v-if="hasPendingPlanChange" class="rounded-xl border border-amber-300 bg-amber-50 p-3 text-sm text-amber-900 dark:border-amber-800 dark:bg-amber-950/30 dark:text-amber-200">
                Salve a alteração de plano para atualizar o valor e as parcelas do checkout.
              </div>
              <fieldset>
                <legend class="mb-3 text-sm font-semibold text-foreground">Forma de pagamento</legend>
                <div class="grid gap-3">
                  <button
                    v-for="method in paymentMethods"
                    :key="method.value"
                    type="button"
                    class="group relative flex w-full items-center gap-3 rounded-xl border p-3 text-left transition-all"
                    :class="payForm.method === method.value
                      ? 'border-emerald-500 bg-emerald-50 ring-2 ring-emerald-500/15 dark:bg-emerald-950/30'
                      : 'border-border bg-background hover:border-emerald-300 hover:bg-muted/50'"
                    @click="payForm.method = method.value"
                  >
                    <div
                      class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border"
                      :class="payForm.method === method.value
                        ? 'border-emerald-200 bg-white text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-300'
                        : 'border-border bg-muted text-muted-foreground'"
                    >
                      <component :is="method.icon" class="h-5 w-5" />
                    </div>
                    <div class="min-w-0 flex-1">
                      <p class="font-semibold text-foreground">{{ method.label }}</p>
                      <p class="mt-0.5 text-xs leading-relaxed text-muted-foreground">{{ method.description }}</p>
                      <p class="mt-1 text-[11px] font-medium text-emerald-700 dark:text-emerald-400">{{ method.detail }}</p>
                    </div>
                    <div
                      class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border"
                      :class="payForm.method === method.value ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-border'"
                    >
                      <Check v-if="payForm.method === method.value" class="h-3 w-3" stroke-width="3" />
                    </div>
                  </button>
                </div>
              </fieldset>

              <form v-show="payForm.method === 'credit_card' && !hasPendingPlanChange" id="form-checkout" class="space-y-3 rounded-xl border border-border bg-muted/20 p-4">
                <p class="text-sm font-semibold text-foreground">Dados do cartão</p>
                <p class="text-xs text-muted-foreground">Os dados são enviados diretamente ao Mercado Pago e não ficam armazenados na Vitrine.</p>
                <div id="form-checkout__cardNumber" class="h-11 rounded-lg border border-input bg-background px-3 py-2"></div>
                <div class="grid grid-cols-2 gap-3">
                  <div id="form-checkout__expirationDate" class="h-11 rounded-lg border border-input bg-background px-3 py-2"></div>
                  <div id="form-checkout__securityCode" class="h-11 rounded-lg border border-input bg-background px-3 py-2"></div>
                </div>
                <input id="form-checkout__cardholderName" type="text" class="h-11 w-full rounded-lg border border-input bg-background px-3 text-foreground" />
                <select id="form-checkout__issuer" class="h-11 w-full rounded-lg border border-input bg-background px-3 text-foreground"></select>
                <select id="form-checkout__installments" class="h-11 w-full rounded-lg border border-input bg-background px-3 text-foreground"></select>
                <div class="grid grid-cols-3 gap-3">
                  <select id="form-checkout__identificationType" class="h-11 rounded-lg border border-input bg-background px-2 text-foreground"></select>
                  <input id="form-checkout__identificationNumber" type="text" class="col-span-2 h-11 rounded-lg border border-input bg-background px-3 text-foreground" />
                </div>
                <input id="form-checkout__cardholderEmail" type="email" class="h-11 w-full rounded-lg border border-input bg-background px-3 text-foreground" />
                <p v-if="cardError || payForm.errors.token" class="text-sm text-destructive">{{ cardError || payForm.errors.token }}</p>
                <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 font-semibold text-white disabled:opacity-50" :disabled="!cardReady || payForm.processing">
                  <LoaderCircle v-if="payForm.processing" class="h-4 w-4 animate-spin" />
                  {{ cardReady ? 'Pagar com cartão' : 'Carregando checkout seguro...' }}
                </button>
              </form>

              <div class="rounded-xl border border-border bg-muted/40 p-4">
                <div class="flex items-center justify-between gap-3 text-sm">
                  <span class="text-muted-foreground">Plano</span>
                  <span class="font-semibold text-foreground">{{ subscription.plan_name }}</span>
                </div>
                <div class="mt-2 flex items-center justify-between gap-3 text-sm">
                  <span class="text-muted-foreground">Ciclo</span>
                  <span class="font-medium text-foreground">{{ subscription.billing_period === 'annual' ? 'Anual' : 'Mensal' }}</span>
                </div>
                <div class="my-3 border-t border-border"></div>
                <div class="flex items-end justify-between gap-3">
                  <div>
                    <p class="text-xs text-muted-foreground">Total a pagar</p>
                    <p class="mt-1 text-2xl font-bold text-foreground">{{ formatCurrency(subscription.price) }}</p>
                    <p v-if="payForm.method === 'credit_card'" class="mt-1 text-[11px] text-muted-foreground">{{ subscription.billing_period === 'monthly' ? 'Pagamento mensal somente à vista.' : 'Selecione as parcelas acima para ver os juros e o total calculados pelo Mercado Pago.' }}</p>
                  </div>
                  <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                    {{ selectedPaymentMethod?.label }}
                  </span>
                </div>
              </div>

              <p v-if="payForm.errors.method" class="text-sm text-destructive">{{ payForm.errors.method }}</p>

              <button
                v-if="payForm.method === 'pix'"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 font-semibold text-white shadow-sm transition hover:bg-emerald-700 hover:shadow-md disabled:cursor-not-allowed disabled:opacity-50"
                :disabled="payForm.processing || !payment_gateway.configured || hasPendingPlanChange"
                @click="payNow"
              >
                <LoaderCircle v-if="payForm.processing" class="h-4 w-4 animate-spin" />
                <template v-else>
                  Confirmar pagamento
                  <ArrowRight class="h-4 w-4" />
                </template>
              </button>

              <p class="flex items-center justify-center gap-1.5 text-center text-xs text-muted-foreground">
                <ShieldCheck class="h-3.5 w-3.5" />
                Seus dados de pagamento são protegidos.
              </p>

              <Link
                v-if="!is_blocked"
                :href="route('painel.index')"
                class="block text-center text-sm text-muted-foreground hover:text-foreground"
              >
                Voltar ao painel
              </Link>
            </div>
          </div>

          <div class="rounded-2xl border border-red-200 bg-red-50 p-5 md:p-6 space-y-2">
            <p class="font-semibold text-red-700 text-sm">Excluir conta</p>
            <p class="text-xs text-red-700">
              Se você não quiser continuar, pode remover definitivamente a conta.
            </p>
            <input
              v-model="deleteForm.password"
              type="password"
              placeholder="Digite sua senha"
              class="w-full border rounded-lg p-2"
            />
            <button
              class="w-full rounded-lg bg-red-600 text-white py-2.5 font-semibold disabled:opacity-50"
              :disabled="deleteForm.processing"
              @click="deleteAccount"
            >
              Deletar conta
            </button>
          </div>
        </div>
      </section>
    </div>
  </AppLayout>
</template>
