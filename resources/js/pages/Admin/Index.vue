<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import {
  Activity, BadgeDollarSign, Boxes, CalendarClock, CheckCircle2,
  Clock3, CreditCard, KeyRound, Package, Pencil, Search, Settings, ShieldCheck, Store, Users, X,
} from 'lucide-vue-next'
import { computed, ref } from 'vue'

type Limits = { products: number | null; product_images: number | null; gallery_images: number | null; banners: number | null }
type Subscription = {
  id: number; plan_id: number; plan_name: string; custom_plan_name: string | null; price: number
  billing_period: 'monthly' | 'annual'; status: string; trial_ends_at: string | null
  next_billing_at: string | null; days_left: number | null; status_changed_at: string | null; days_in_status: number; limits: Limits
}
type Client = {
  id: number; name: string; email: string; business_name: string | null; slug: string | null
  is_active: boolean; created_at: string
  usage: { products: number; product_images: number; pages: number; banners: number; categories: number; customers: number }
  subscription: Subscription | null
}

const props = defineProps<{
  stats: { clients: number; active: number; trial: number; expiring_trials: number; paid_revenue: number; paid_payments: number; pending_payments: number; expired: number; cancelled: number }
  clients: { data: Client[]; links: Array<{ url: string | null; label: string; active: boolean }>; from: number | null; to: number | null; total: number }
  payments: Array<{ id: number; customer: string | null; email: string | null; amount: number; method: string; status: string; paid_at: string | null; created_at: string }>
  plans: Array<{ id: number; name: string; price: number; products_limit: number; product_images_limit: number | null; gallery_images_limit: number; banners_limit: number }>
  filters: { search: string; status: string }
  payment_settings: { configured: boolean; access_token_hint: string | null; public_key_hint: string | null; webhook_secret_hint: string | null; sandbox: boolean; pix_enabled: boolean; credit_card_enabled: boolean; max_installments: number; webhook_url: string }
}>()

const search = ref(props.filters.search)
const status = ref(props.filters.status)
const activeTab = ref<'clients' | 'payments' | 'settings'>('clients')
const editingClient = ref<Client | null>(null)
const customEnabled = ref(false)

const form = useForm({
  plan_id: props.plans[0]?.id ?? 0,
  custom_plan_name: '', price: 0, billing_period: 'monthly', status: 'trial',
  trial_ends_at: '', next_billing_at: '', custom_products_limit: null as number | null,
  custom_product_images_limit: null as number | null, custom_gallery_images_limit: null as number | null,
  custom_banners_limit: null as number | null,
})
const paymentSettingsForm = useForm({ access_token: '', public_key: '', webhook_secret: '', sandbox: props.payment_settings.sandbox, pix_enabled: props.payment_settings.pix_enabled, credit_card_enabled: props.payment_settings.credit_card_enabled, max_installments: props.payment_settings.max_installments })
function savePaymentSettings() { paymentSettingsForm.put('/admin/configuracoes/pagamentos', { preserveScroll: true, onSuccess: () => paymentSettingsForm.reset('access_token', 'public_key', 'webhook_secret') }) }

const statCards = computed(() => [
  { label: 'Clientes', value: props.stats.clients, detail: `${props.stats.active} com plano ativo`, icon: Users, color: 'text-blue-600 bg-blue-100 dark:bg-blue-950 dark:text-blue-300' },
  { label: 'Em período trial', value: props.stats.trial, detail: `${props.stats.expiring_trials} expiram em até 7 dias`, icon: Clock3, color: 'text-amber-600 bg-amber-100 dark:bg-amber-950 dark:text-amber-300' },
  { label: 'Receita confirmada', value: formatCurrency(props.stats.paid_revenue), detail: `${props.stats.paid_payments} pagamentos concluídos`, icon: BadgeDollarSign, color: 'text-emerald-600 bg-emerald-100 dark:bg-emerald-950 dark:text-emerald-300' },
  { label: 'Expirados ou cancelados', value: props.stats.expired + props.stats.cancelled, detail: `${props.stats.expired} expirados e ${props.stats.cancelled} cancelados`, icon: CreditCard, color: 'text-red-600 bg-red-100 dark:bg-red-950 dark:text-red-300' },
])

function applyFilters() {
  router.get('/admin', { search: search.value || undefined, status: status.value || undefined }, { preserveState: true, replace: true })
}

function openPlan(client: Client) {
  editingClient.value = client
  const subscription = client.subscription
  customEnabled.value = Boolean(subscription?.custom_plan_name)
  form.clearErrors()
  form.plan_id = subscription?.plan_id ?? props.plans[0]?.id ?? 0
  form.custom_plan_name = subscription?.custom_plan_name ?? ''
  form.price = subscription?.price ?? props.plans.find((plan) => plan.id === form.plan_id)?.price ?? 0
  form.billing_period = subscription?.billing_period ?? 'monthly'
  form.status = subscription?.status ?? 'trial'
  form.trial_ends_at = subscription?.trial_ends_at ?? ''
  form.next_billing_at = subscription?.next_billing_at ?? ''
  form.custom_products_limit = subscription?.limits.products ?? null
  form.custom_product_images_limit = subscription?.limits.product_images ?? null
  form.custom_gallery_images_limit = subscription?.limits.gallery_images ?? null
  form.custom_banners_limit = subscription?.limits.banners ?? null
}

function chooseBasePlan() {
  const plan = props.plans.find((item) => item.id === form.plan_id)
  if (!plan || customEnabled.value) return
  form.price = plan.price
  form.custom_products_limit = null
  form.custom_product_images_limit = null
  form.custom_gallery_images_limit = null
  form.custom_banners_limit = null
}

function savePlan() {
  if (!editingClient.value) return
  if (!customEnabled.value) {
    form.custom_plan_name = ''
    form.custom_products_limit = null
    form.custom_product_images_limit = null
    form.custom_gallery_images_limit = null
    form.custom_banners_limit = null
  }
  form.put(`/admin/clientes/${editingClient.value.id}/assinatura`, {
    preserveScroll: true,
    onSuccess: () => { editingClient.value = null },
  })
}

function usagePercent(value: number, limit: number | null) {
  if (limit === null || limit <= 0) return limit === 0 ? 100 : 0
  return Math.min(100, Math.round((value / limit) * 100))
}

function formatCurrency(value: number) { return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value) }
function formatDateTime(value: string | null) { return value ? new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short', timeStyle: 'short' }).format(new Date(value)) : '—' }
function statusLabel(value: string) { return ({ trial: 'Período de teste', active: 'Ativo', past_due: 'Em atraso', cancelled: 'Cancelado', expired: 'Expirado', paid: 'Concluído', pending: 'Pendente', failed: 'Falhou', refunded: 'Estornado' } as Record<string, string>)[value] ?? value }
function methodLabel(value: string) { return ({ pix: 'PIX', credit_card: 'Cartão', boleto: 'Boleto' } as Record<string, string>)[value] ?? value }
function statusClass(value: string) {
  if (['active', 'paid'].includes(value)) return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
  if (['trial', 'pending'].includes(value)) return 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300'
  return 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300'
}
</script>

<template>
  <Head title="Administração da plataforma" />
  <AppLayout :breadcrumbs="[{ title: 'Administração', href: '/admin' }]">
    <div class="space-y-6 p-4 md:p-6">
      <header class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
        <div>
          <div class="flex items-center gap-2 text-sm font-medium text-primary"><ShieldCheck class="h-4 w-4" /> Área do proprietário</div>
          <h1 class="mt-1 text-2xl font-bold text-foreground md:text-3xl">Administração da plataforma</h1>
          <p class="mt-1 text-sm text-muted-foreground">Clientes, assinaturas, consumo de recursos e pagamentos em um só lugar.</p>
        </div>
      </header>

      <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article v-for="card in statCards" :key="card.label" class="rounded-2xl border border-border bg-card p-5 shadow-sm">
          <div class="flex items-start justify-between gap-3">
            <div><p class="text-sm text-muted-foreground">{{ card.label }}</p><p class="mt-2 text-2xl font-bold text-foreground">{{ card.value }}</p></div>
            <div class="rounded-xl p-2.5" :class="card.color"><component :is="card.icon" class="h-5 w-5" /></div>
          </div>
          <p class="mt-3 text-xs text-muted-foreground">{{ card.detail }}</p>
        </article>
      </section>

      <div class="flex w-fit rounded-xl border border-border bg-muted/40 p-1">
        <button class="rounded-lg px-4 py-2 text-sm font-medium" :class="activeTab === 'clients' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground'" @click="activeTab = 'clients'">Clientes</button>
        <button class="rounded-lg px-4 py-2 text-sm font-medium" :class="activeTab === 'payments' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground'" @click="activeTab = 'payments'">Pagamentos</button>
        <button class="rounded-lg px-4 py-2 text-sm font-medium" :class="activeTab === 'settings' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground'" @click="activeTab = 'settings'">Configurações</button>
      </div>

      <section v-if="activeTab === 'clients'" class="space-y-4">
        <div class="flex flex-col gap-3 rounded-2xl border border-border bg-card p-4 md:flex-row">
          <div class="relative flex-1"><Search class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" /><input v-model="search" class="w-full rounded-xl border border-input bg-background py-2.5 pl-10 pr-3 text-sm text-foreground" placeholder="Buscar por cliente, loja ou e-mail" @keyup.enter="applyFilters" /></div>
          <select v-model="status" class="rounded-xl border border-input bg-background px-3 py-2.5 text-sm text-foreground" @change="applyFilters">
            <option value="">Todos os status</option><option value="trial">Período de teste</option><option value="active">Ativo</option><option value="past_due">Em atraso</option><option value="expired">Expirado</option><option value="cancelled">Cancelado</option>
          </select>
          <button class="rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground" @click="applyFilters">Filtrar</button>
        </div>

        <div v-if="!clients.data.length" class="rounded-2xl border border-dashed border-border p-12 text-center text-muted-foreground">Nenhum cliente encontrado.</div>
        <div v-else class="grid gap-4 xl:grid-cols-2">
          <article v-for="client in clients.data" :key="client.id" class="rounded-2xl border border-border bg-card p-5 shadow-sm">
            <div class="flex items-start justify-between gap-3">
              <div class="flex min-w-0 items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary/10 font-bold text-primary">{{ client.name.charAt(0).toUpperCase() }}</div>
                <div class="min-w-0"><h2 class="truncate font-semibold text-foreground">{{ client.business_name || client.name }}</h2><p class="truncate text-xs text-muted-foreground">{{ client.email }}</p></div>
              </div>
              <div class="flex shrink-0 gap-2"><Link :href="`/admin/clientes/${client.id}`" class="rounded-lg bg-primary px-3 py-2 text-xs font-semibold text-primary-foreground">Ver CRM</Link><button class="flex items-center gap-1.5 rounded-lg border border-border px-3 py-2 text-xs font-semibold text-foreground hover:bg-muted" @click="openPlan(client)"><Pencil class="h-3.5 w-3.5" /> Plano</button></div>
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-2 text-xs">
              <span class="rounded-full px-2.5 py-1 font-semibold" :class="statusClass(client.subscription?.status ?? 'expired')">{{ client.subscription ? statusLabel(client.subscription.status) : 'Sem assinatura' }}</span>
              <span class="rounded-full bg-muted px-2.5 py-1 text-muted-foreground">{{ client.subscription?.plan_name ?? 'Sem plano' }}</span>
              <span v-if="client.subscription?.days_left !== null" class="flex items-center gap-1 text-muted-foreground"><CalendarClock class="h-3.5 w-3.5" /> {{ Math.max(0, client.subscription?.days_left ?? 0) }} dias restantes</span>
              <span v-if="['expired', 'cancelled'].includes(client.subscription?.status ?? '')" class="text-red-600 dark:text-red-400">Há {{ client.subscription?.days_in_status }} dias neste status</span>
            </div>

            <div class="mt-5 grid grid-cols-2 gap-x-5 gap-y-4">
              <div v-for="item in [
                { label: 'Produtos', value: client.usage.products, limit: client.subscription?.limits.products, icon: Package },
                { label: 'Fotos de produtos', value: client.usage.product_images, limit: client.subscription?.limits.product_images, icon: Boxes },
                { label: 'Páginas', value: client.usage.pages, limit: null, icon: Activity },
                { label: 'Banners', value: client.usage.banners, limit: client.subscription?.limits.banners, icon: Store },
              ]" :key="item.label">
                <div class="flex justify-between text-xs"><span class="text-muted-foreground">{{ item.label }}</span><b class="text-foreground">{{ item.value }} / {{ item.limit === null ? '∞' : item.limit }}</b></div>
                <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-muted"><div class="h-full rounded-full bg-primary" :style="{ width: `${usagePercent(item.value, item.limit ?? null)}%` }"></div></div>
              </div>
            </div>
          </article>
        </div>

        <div v-if="clients.links.length > 3" class="flex flex-wrap justify-center gap-1">
          <Link v-for="link in clients.links" :key="link.label" :href="link.url || '#'" class="rounded-lg border border-border px-3 py-2 text-sm" :class="link.active ? 'bg-primary text-primary-foreground' : 'bg-card text-muted-foreground'"><span v-html="link.label"></span></Link>
        </div>
      </section>

      <section v-else-if="activeTab === 'payments'" class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm">
        <div class="border-b border-border p-5"><h2 class="font-semibold text-foreground">Pagamentos recentes</h2><p class="text-sm text-muted-foreground">Últimos 30 registros processados pela plataforma.</p></div>
        <div class="overflow-x-auto"><table class="w-full min-w-[760px] text-sm"><thead class="bg-muted/50 text-left text-xs uppercase text-muted-foreground"><tr><th class="px-5 py-3">Cliente</th><th class="px-5 py-3">Método</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Data</th><th class="px-5 py-3 text-right">Valor</th></tr></thead><tbody class="divide-y divide-border"><tr v-for="payment in payments" :key="payment.id"><td class="px-5 py-4"><b class="text-foreground">{{ payment.customer || 'Cliente removido' }}</b><p class="text-xs text-muted-foreground">{{ payment.email }}</p></td><td class="px-5 py-4 text-foreground">{{ methodLabel(payment.method) }}</td><td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusClass(payment.status)">{{ statusLabel(payment.status) }}</span></td><td class="px-5 py-4 text-muted-foreground">{{ formatDateTime(payment.paid_at || payment.created_at) }}</td><td class="px-5 py-4 text-right font-semibold text-foreground">{{ formatCurrency(payment.amount) }}</td></tr><tr v-if="!payments.length"><td colspan="5" class="px-5 py-10 text-center text-muted-foreground">Nenhum pagamento registrado.</td></tr></tbody></table></div>
      </section>

      <section v-else class="mx-auto w-full max-w-3xl overflow-hidden rounded-2xl border border-border bg-card shadow-sm">
        <header class="border-b border-border bg-muted/40 p-5 md:p-6"><div class="flex items-start gap-3"><div class="rounded-xl bg-blue-100 p-3 text-blue-700 dark:bg-blue-950 dark:text-blue-300"><KeyRound class="h-5 w-5" /></div><div><h2 class="font-semibold text-foreground">Mercado Pago</h2><p class="text-sm text-muted-foreground">Credenciais usadas para gerar PIX e confirmar pagamentos.</p></div><span class="ml-auto rounded-full px-2.5 py-1 text-xs font-semibold" :class="payment_settings.configured ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300'">{{ payment_settings.configured ? 'Configurado' : 'Pendente' }}</span></div></header>
        <form class="space-y-5 p-5 md:p-6" @submit.prevent="savePaymentSettings">
          <div class="rounded-xl border border-border bg-muted/40 p-4 text-sm text-muted-foreground"><b class="text-foreground">Segurança:</b> as credenciais são criptografadas no banco. Depois de salvar, apenas os últimos caracteres serão exibidos.</div>
          <label class="block text-sm text-foreground">Access Token privado <span class="text-destructive">*</span><input v-model="paymentSettingsForm.access_token" type="password" autocomplete="new-password" class="mt-1.5 w-full rounded-xl border border-input bg-background p-3" :placeholder="payment_settings.access_token_hint || 'APP_USR-... ou TEST-...'" /><span class="mt-1 block text-xs text-muted-foreground">{{ payment_settings.access_token_hint ? `Atual: ${payment_settings.access_token_hint}. Deixe vazio para manter.` : 'Obrigatório para criar pagamentos no servidor.' }}</span></label>
          <label class="block text-sm text-foreground">Public Key<input v-model="paymentSettingsForm.public_key" type="password" autocomplete="new-password" class="mt-1.5 w-full rounded-xl border border-input bg-background p-3" :placeholder="payment_settings.public_key_hint || 'APP_USR-... ou TEST-...'" /><span class="mt-1 block text-xs text-muted-foreground">Obrigatória para tokenizar cartões com segurança no navegador.</span></label>
          <label class="block text-sm text-foreground">Assinatura secreta do webhook<input v-model="paymentSettingsForm.webhook_secret" type="password" autocomplete="new-password" class="mt-1.5 w-full rounded-xl border border-input bg-background p-3" :placeholder="payment_settings.webhook_secret_hint || 'Chave secreta das notificações'" /></label>
          <label class="flex items-center justify-between gap-3 rounded-xl border border-border p-4"><span><b class="block text-sm text-foreground">Ambiente de testes</b><span class="text-xs text-muted-foreground">Use credenciais TEST antes de receber pagamentos reais.</span></span><input v-model="paymentSettingsForm.sandbox" type="checkbox" class="h-5 w-5 accent-primary" /></label>
          <div class="grid gap-3 sm:grid-cols-2">
            <label class="flex items-center justify-between rounded-xl border border-border p-4 text-sm text-foreground"><span>Receber por PIX</span><input v-model="paymentSettingsForm.pix_enabled" type="checkbox" class="h-5 w-5 accent-primary" /></label>
            <label class="flex items-center justify-between rounded-xl border border-border p-4 text-sm text-foreground"><span>Receber por cartão</span><input v-model="paymentSettingsForm.credit_card_enabled" type="checkbox" class="h-5 w-5 accent-primary" /></label>
          </div>
          <label class="block text-sm text-foreground">Limite de parcelas<input v-model.number="paymentSettingsForm.max_installments" type="number" min="1" max="12" class="mt-1.5 w-full rounded-xl border border-input bg-background p-3" /><span class="mt-1 block text-xs text-muted-foreground">Teto permitido pela plataforma. A quantidade disponível e os juros são definidos pelo Mercado Pago conforme sua conta e o cartão.</span></label>
          <div><p class="text-sm font-medium text-foreground">URL para notificações</p><code class="mt-1.5 block break-all rounded-xl bg-muted p-3 text-xs text-foreground">{{ payment_settings.webhook_url }}</code><p class="mt-1 text-xs text-muted-foreground">Cadastre esta URL em “Webhooks” na sua integração do Mercado Pago.</p></div>
          <p v-if="Object.keys(paymentSettingsForm.errors).length" class="rounded-lg bg-destructive/10 p-3 text-sm text-destructive">Revise as credenciais informadas.</p>
          <button class="flex w-full items-center justify-center gap-2 rounded-xl bg-primary p-3 font-semibold text-primary-foreground disabled:opacity-50" :disabled="paymentSettingsForm.processing"><Settings class="h-4 w-4" /> Salvar configurações</button>
        </form>
      </section>
    </div>

    <div v-if="editingClient" class="fixed inset-0 z-50 flex items-end justify-center bg-black/55 p-0 sm:items-center sm:p-4" @click.self="editingClient = null">
      <div class="max-h-[92vh] w-full max-w-2xl overflow-y-auto rounded-t-2xl border border-border bg-card shadow-2xl sm:rounded-2xl">
        <header class="sticky top-0 z-10 flex items-start justify-between border-b border-border bg-card p-5"><div><h2 class="text-lg font-bold text-foreground">Plano de {{ editingClient.business_name || editingClient.name }}</h2><p class="text-sm text-muted-foreground">Configure a assinatura, validade e limites exclusivos.</p></div><button class="rounded-lg p-2 text-muted-foreground hover:bg-muted" @click="editingClient = null"><X class="h-5 w-5" /></button></header>
        <div class="space-y-5 p-5">
          <div class="grid gap-4 sm:grid-cols-2">
            <label class="text-sm text-foreground">Plano base<select v-model="form.plan_id" class="mt-1.5 w-full rounded-lg border border-input bg-background p-2.5" @change="chooseBasePlan"><option v-for="plan in plans" :key="plan.id" :value="plan.id">{{ plan.name }}</option></select></label>
            <label class="text-sm text-foreground">Status<select v-model="form.status" class="mt-1.5 w-full rounded-lg border border-input bg-background p-2.5"><option value="trial">Período de teste</option><option value="active">Ativo</option><option value="past_due">Em atraso</option><option value="expired">Expirado</option><option value="cancelled">Cancelado</option></select></label>
            <label class="text-sm text-foreground">Valor cobrado<input v-model.number="form.price" type="number" min="0" step="0.01" class="mt-1.5 w-full rounded-lg border border-input bg-background p-2.5" /></label>
            <label class="text-sm text-foreground">Ciclo<select v-model="form.billing_period" class="mt-1.5 w-full rounded-lg border border-input bg-background p-2.5"><option value="monthly">Mensal</option><option value="annual">Anual</option></select></label>
            <label class="text-sm text-foreground">Fim do trial<input v-model="form.trial_ends_at" type="date" class="mt-1.5 w-full rounded-lg border border-input bg-background p-2.5" /></label>
            <label class="text-sm text-foreground">Próxima cobrança<input v-model="form.next_billing_at" type="date" class="mt-1.5 w-full rounded-lg border border-input bg-background p-2.5" /></label>
          </div>

          <label class="flex items-center justify-between gap-3 rounded-xl border border-border bg-muted/40 p-4"><span><b class="block text-sm text-foreground">Plano personalizado</b><span class="text-xs text-muted-foreground">Defina nome e limites exclusivos para este cliente.</span></span><input v-model="customEnabled" type="checkbox" class="h-5 w-5 accent-primary" /></label>
          <div v-if="customEnabled" class="space-y-4 rounded-xl border border-primary/30 bg-primary/5 p-4">
            <label class="block text-sm text-foreground">Nome do plano<input v-model="form.custom_plan_name" class="mt-1.5 w-full rounded-lg border border-input bg-background p-2.5" placeholder="Ex.: Plano Parceiro" /></label>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
              <label class="text-xs text-foreground">Produtos<input v-model.number="form.custom_products_limit" type="number" min="0" class="mt-1.5 w-full rounded-lg border border-input bg-background p-2.5" /></label>
              <label class="text-xs text-foreground">Fotos/produto<input v-model.number="form.custom_product_images_limit" type="number" min="0" class="mt-1.5 w-full rounded-lg border border-input bg-background p-2.5" /></label>
              <label class="text-xs text-foreground">Galeria<input v-model.number="form.custom_gallery_images_limit" type="number" min="0" class="mt-1.5 w-full rounded-lg border border-input bg-background p-2.5" /></label>
              <label class="text-xs text-foreground">Banners<input v-model.number="form.custom_banners_limit" type="number" min="0" class="mt-1.5 w-full rounded-lg border border-input bg-background p-2.5" /></label>
            </div>
          </div>
          <div v-if="Object.keys(form.errors).length" class="rounded-xl bg-destructive/10 p-3 text-sm text-destructive">Revise os campos informados antes de salvar.</div>
        </div>
        <footer class="sticky bottom-0 flex justify-end gap-3 border-t border-border bg-card p-5"><button class="rounded-xl border border-border px-4 py-2.5 text-sm text-foreground" @click="editingClient = null">Cancelar</button><button class="flex items-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground disabled:opacity-50" :disabled="form.processing" @click="savePlan"><CheckCircle2 class="h-4 w-4" /> Salvar plano</button></footer>
      </div>
    </div>
  </AppLayout>
</template>
