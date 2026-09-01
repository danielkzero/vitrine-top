<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import moment from 'moment'
import 'moment/locale/pt-br'
import { computed, ref } from 'vue'

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
  }
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
    ? selectedPlan.value.annual_monthly_equivalent
    : selectedPlan.value.monthly
})

const planForm = useForm({
  plan_code: selectedPlanCode.value,
  billing_period: selectedPeriod.value,
})

const payForm = useForm({
  method: 'pix',
})

const deleteForm = useForm({
  password: '',
})

function applyPlanSelection() {
  planForm.plan_code = selectedPlanCode.value
  planForm.billing_period = selectedPeriod.value
  planForm.post(route('painel.billing.plan'))
}

function payNow() {
  payForm.post(route('painel.billing.pay'))
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
                <p class="font-semibold text-slate-900">{{ formatCurrency(subscription.price) }}/mês</p>
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
              <p class="text-sm text-slate-700">Valor: {{ formatCurrency(selectedPrice) }}/mês</p>
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
                <p class="text-slate-700">{{ methodLabel(payment.method) }} - {{ statusLabel(payment.status) }}</p>
                <p class="text-slate-700">{{ formatDateTime(payment.paid_at ?? payment.created_at) }}</p>
                <p class="font-semibold text-slate-900 md:text-right">{{ formatCurrency(payment.amount) }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="space-y-6">
          <div class="rounded-2xl border bg-white p-5 md:p-6 space-y-3">
            <h2 class="text-lg font-semibold text-slate-900">Efetuar pagamento</h2>
            <p class="text-sm text-slate-600">
              Gateway preparado: <b>{{ payment_gateway.provider }}</b>
            </p>
            <label class="text-sm font-medium text-slate-700">Método</label>
            <select v-model="payForm.method" class="w-full border rounded-lg p-2">
              <option v-if="payment_gateway.supports_pix" value="pix">PIX</option>
              <option v-if="payment_gateway.supports_credit_card" value="credit_card">Cartão de crédito</option>
              <option value="boleto">Boleto</option>
            </select>

            <button
              class="w-full rounded-lg bg-emerald-600 text-white py-2.5 font-semibold disabled:opacity-50"
              :disabled="payForm.processing"
              @click="payNow"
            >
              Confirmar pagamento
            </button>

            <Link
              v-if="!is_blocked"
              :href="route('painel.index')"
              class="block text-center text-sm text-slate-600 hover:text-slate-900"
            >
              Voltar ao painel
            </Link>
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

