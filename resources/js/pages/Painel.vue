<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head } from '@inertiajs/vue3'
import BaseButton from '@/components/ui/button/BaseButton.vue'
import { getIcon } from '@/lib/iconMap'
import { formatCurrency, formatNumber } from '@/lib/utils'
import { route } from 'ziggy-js'
import axios from 'axios'
import { computed, onMounted, ref } from 'vue'

const props = defineProps<{
  stats: {
    products: { imagesCount: number; total: number }
    revenue: { total: number; count: number }
    resources: {
      products: { used: number; limit: number | null }
      product_images: { used: number; limit: number | null }
      gallery_images: { used: number; limit: number | null }
      banners: { used: number; limit: number | null }
    }
  }
  user: {
    business_name?: string
    slug?: string
  }
}>()

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Painel', href: route('painel.index') },
]

const loadingAnalytics = ref(true)
const loadingOrders = ref(true)

const analytics = ref<any>({
  total_orders: 0,
  total_revenue: 0,
  customers_count: 0,
  visits_count: 0,
  page_views_count: 0,
  product_views_count: 0,
  top_products: [],
})

const orders = ref<any[]>([])
const periodRows = ref<any[]>([])
const selectedRange = ref<'7d' | '30d' | '90d'>('30d')

const summaryCards = computed(() => [
  {
    label: 'Pedidos',
    value: formatNumber(analytics.value.total_orders),
    icon: 'ReceiptText',
    color: 'text-sky-700 dark:text-sky-300',
    accent: 'bg-sky-400',
  },
  {
    label: 'Faturamento',
    value: formatCurrency(analytics.value.total_revenue),
    icon: 'CircleDollarSign',
    color: 'text-emerald-700 dark:text-emerald-300',
    accent: 'bg-emerald-400',
  },
  {
    label: 'Clientes',
    value: formatNumber(analytics.value.customers_count),
    icon: 'UsersRound',
    color: 'text-indigo-700 dark:text-indigo-300',
    accent: 'bg-indigo-400',
  },
  {
    label: 'Visitas',
    value: formatNumber(analytics.value.visits_count),
    icon: 'MousePointerClick',
    color: 'text-amber-700 dark:text-amber-300',
    accent: 'bg-amber-400',
  },
  {
    label: 'Páginas vistas',
    value: formatNumber(analytics.value.page_views_count),
    icon: 'FileText',
    color: 'text-fuchsia-700 dark:text-fuchsia-300',
    accent: 'bg-fuchsia-400',
  },
  {
    label: 'Produtos vistos',
    value: formatNumber(analytics.value.product_views_count),
    icon: 'PackageSearch',
    color: 'text-cyan-700 dark:text-cyan-300',
    accent: 'bg-cyan-400',
  },
])

const resourceCards = computed(() => [
  {
    label: 'Produtos',
    used: Number(props.stats?.resources?.products?.used ?? 0),
    limit: props.stats?.resources?.products?.limit,
  },
  {
    label: 'Fotos por produto',
    used: Number(props.stats?.resources?.product_images?.used ?? 0),
    limit: props.stats?.resources?.product_images?.limit,
  },
  {
    label: 'Fotos da galeria',
    used: Number(props.stats?.resources?.gallery_images?.used ?? 0),
    limit: props.stats?.resources?.gallery_images?.limit,
  },
  {
    label: 'Banners',
    used: Number(props.stats?.resources?.banners?.used ?? 0),
    limit: props.stats?.resources?.banners?.limit,
  },
])

function rangeToDates(range: '7d' | '30d' | '90d') {
  const end = new Date()
  const start = new Date()

  if (range === '7d') start.setDate(end.getDate() - 7)
  if (range === '30d') start.setDate(end.getDate() - 30)
  if (range === '90d') start.setDate(end.getDate() - 90)

  return {
    start: start.toISOString().slice(0, 10),
    end: end.toISOString().slice(0, 10),
  }
}

async function loadAnalytics() {
  loadingAnalytics.value = true
  const { start, end } = rangeToDates(selectedRange.value)

  try {
    const [summaryResponse, periodResponse] = await Promise.all([
      axios.get('/api/admin/analytics/summary', { params: { start, end } }),
      axios.get('/api/admin/analytics/orders-by-period', { params: { start, end, group_by: 'day' } }),
    ])

    analytics.value = summaryResponse.data
    periodRows.value = periodResponse.data?.data ?? []
  } finally {
    loadingAnalytics.value = false
  }
}

async function loadOrders() {
  loadingOrders.value = true

  try {
    const response = await axios.get('/api/admin/orders', { params: { per_page: 10 } })
    orders.value = response.data?.data ?? []
  } finally {
    loadingOrders.value = false
  }
}

const maxRevenue = computed(() => {
  const values = periodRows.value.map((row: any) => Number(row.revenue ?? 0))
  return Math.max(1, ...values)
})

function barWidth(row: any) {
  const value = Number(row.revenue ?? 0)
  return `${Math.max(4, (value / maxRevenue.value) * 100)}%`
}

async function updateOrderStatus(orderId: number, status: string) {
  await axios.patch(`/api/admin/orders/${orderId}/status`, { status })
  await loadOrders()
  await loadAnalytics()
}

function orderStatusLabel(status: string) {
  return ({
    pending: 'Pendente',
    confirmed: 'Confirmado',
    preparing: 'Em preparação',
    shipped: 'Enviado',
    delivered: 'Entregue',
    canceled: 'Cancelado',
  } as Record<string, string>)[status] ?? status
}

onMounted(async () => {
  await Promise.all([loadAnalytics(), loadOrders()])
})
</script>

<template>
  <Head title="Painel" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="container mx-auto px-4 py-6 flex flex-col gap-6">
      <div class="md:flex md:justify-start gap-3 space-y-3">
        <div>
          <h1 class="text-3xl font-bold text-foreground">Painel</h1>
          <p class="text-muted-foreground">Visão geral da sua loja e operação de pedidos</p>
        </div>

        <div class="ms-auto gap-3 flex">
          <BaseButton as="Link" :href="['/settings/store']" variant="primary" size="lg" leading-icon="Settings">Configurar loja</BaseButton>
          <BaseButton v-if="props.user?.business_name && props.user.slug" as="a" :href="[`/${props.user.slug}`]" variant="secondary" size="lg" leading-icon="StoreIcon" target="_blank">Ver vitrine</BaseButton>
        </div>
      </div>

      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <article v-for="card in summaryCards" :key="card.label" class="relative rounded-xl border border-border bg-card p-5 shadow-sm" :class="card.color">
          <div class="absolute left-0 top-0 h-full w-2 rounded-l-xl" :class="card.accent"></div>
          <div class="absolute top-4 right-4 flex h-10 w-10 items-center justify-center rounded-full bg-muted">
            <component :is="getIcon(card.icon)" class="w-5 h-5" />
          </div>
          <p class="font-semibold text-sm">{{ card.label }}</p>
          <h2 class="text-2xl font-bold mt-2">{{ loadingAnalytics ? '...' : card.value }}</h2>
        </article>
      </div>

      <section class="rounded-2xl border border-border bg-card text-card-foreground shadow-sm">
        <div class="border-b border-border p-4">
          <h3 class="font-semibold text-foreground">Consumo do plano</h3>
        </div>
        <div class="p-4 grid md:grid-cols-2 gap-4">
          <article v-for="resource in resourceCards" :key="resource.label" class="border rounded-xl p-4">
            <div class="flex items-center justify-between mb-2">
              <p class="text-sm font-medium text-foreground">{{ resource.label }}</p>
              <p class="text-xs text-muted-foreground">
                {{ formatNumber(resource.used) }} / {{ resource.limit === null ? 'ilimitado' : formatNumber(resource.limit) }}
              </p>
            </div>
            <div class="h-2 overflow-hidden rounded-full bg-muted">
              <div
                class="h-full rounded-full bg-indigo-500"
                :style="{ width: resource.limit && resource.limit > 0 ? `${Math.min(100, (resource.used / resource.limit) * 100)}%` : '100%' }"
              ></div>
            </div>
          </article>
        </div>
      </section>

      <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded-2xl border border-border bg-card text-card-foreground shadow-sm">
          <div class="flex items-center justify-between border-b border-border p-4">
            <h3 class="font-semibold text-foreground">Receita por período</h3>
            <select v-model="selectedRange" class="rounded-lg border border-input bg-background px-2 py-1 text-sm text-foreground" @change="loadAnalytics">
              <option value="7d">7 dias</option>
              <option value="30d">30 dias</option>
              <option value="90d">90 dias</option>
            </select>
          </div>

          <div class="p-4 space-y-3">
            <div v-if="loadingAnalytics" class="text-sm text-muted-foreground">Carregando relatório...</div>
            <div v-else-if="!periodRows.length" class="text-sm text-muted-foreground">Sem dados para o período selecionado.</div>
            <div v-for="row in periodRows" :key="row.period" class="space-y-1">
              <div class="flex items-center justify-between text-xs text-muted-foreground">
                <span>{{ row.period }}</span>
                <span>{{ formatCurrency(row.revenue) }} • {{ formatNumber(row.orders_count) }} pedidos</span>
              </div>
              <div class="h-2 overflow-hidden rounded-full bg-muted">
                <div class="h-full rounded-full bg-sky-500" :style="{ width: barWidth(row) }"></div>
              </div>
            </div>
          </div>
        </section>

        <section class="rounded-2xl border border-border bg-card text-card-foreground shadow-sm">
          <div class="border-b border-border p-4">
            <h3 class="font-semibold text-foreground">Produtos mais vendidos</h3>
          </div>

          <div class="p-4 space-y-2">
            <div v-if="loadingAnalytics" class="text-sm text-muted-foreground">Carregando ranking...</div>
            <div v-else-if="!analytics.top_products?.length" class="text-sm text-muted-foreground">Sem vendas no período.</div>
            <article v-for="product in analytics.top_products" :key="product.product_id" class="flex items-center justify-between border rounded-lg p-3">
              <p class="text-sm font-medium">{{ product.name }}</p>
              <span class="rounded-full bg-muted px-2 py-1 text-xs text-muted-foreground">{{ formatNumber(product.sold_quantity) }} un.</span>
            </article>
          </div>
        </section>
      </div>

      <section class="rounded-2xl border border-border bg-card text-card-foreground shadow-sm">
        <div class="flex items-center justify-between border-b border-border p-4">
          <h3 class="font-semibold text-foreground">Pedidos recentes</h3>
          <BaseButton as="Link" :href="['/painel/cobranca']" variant="ghost" size="sm">Financeiro</BaseButton>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-muted text-muted-foreground">
              <tr>
                <th class="text-left px-4 py-3">Pedido</th>
                <th class="text-left px-4 py-3">Cliente</th>
                <th class="text-left px-4 py-3">Total</th>
                <th class="text-left px-4 py-3">Status</th>
                <th class="text-left px-4 py-3">Ação</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loadingOrders">
                <td class="px-4 py-3 text-muted-foreground" colspan="5">Carregando pedidos...</td>
              </tr>
              <tr v-else-if="!orders.length">
                <td class="px-4 py-3 text-muted-foreground" colspan="5">Nenhum pedido registrado.</td>
              </tr>
              <tr v-for="order in orders" :key="order.id" class="border-t">
                <td class="px-4 py-3 font-medium">{{ order.order_number || `#${order.id}` }}</td>
                <td class="px-4 py-3">{{ order.customer?.name || order.customer_name || '-' }}</td>
                <td class="px-4 py-3">{{ formatCurrency(order.total) }}</td>
                <td class="px-4 py-3">
                  <span class="rounded-full bg-muted px-2 py-1 text-xs text-muted-foreground">{{ orderStatusLabel(order.status) }}</span>
                </td>
                <td class="px-4 py-3">
                  <select class="rounded border border-input bg-background px-2 py-1 text-xs text-foreground" :value="order.status" @change="(e: any) => updateOrderStatus(order.id, e.target.value)">
                    <option value="pending">Pendente</option>
                    <option value="confirmed">Confirmado</option>
                    <option value="preparing">Em preparação</option>
                    <option value="shipped">Enviado</option>
                    <option value="delivered">Entregue</option>
                    <option value="canceled">Cancelado</option>
                  </select>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </AppLayout>
</template>
