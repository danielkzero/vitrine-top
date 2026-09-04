<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { routes } from '@/config/routes'
import { formatCurrency, formatDate } from '@/lib/utils'
import { Head, Link, router } from '@inertiajs/vue3'
import axios from 'axios'

defineProps<{ orders: any; filters: { status?: string } }>()
const breadcrumbs = [{ title: 'Painel', href: routes.painel.index }, { title: 'Pedidos', href: routes.painel.orders.index }]

const labels: Record<string, string> = {
  pending: 'Pendente', confirmed: 'Confirmado', preparing: 'Em preparação',
  shipped: 'Enviado', delivered: 'Entregue', canceled: 'Cancelado',
}

function filter(status: string) {
  router.get(routes.painel.orders.index, status ? { status } : {}, { preserveState: true, replace: true })
}

async function updateStatus(id: number, status: string) {
  await axios.patch(`/api/admin/orders/${id}/status`, { status })
  router.reload({ only: ['orders'] })
}
</script>

<template>
  <Head title="Pedidos" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="container mx-auto space-y-6 px-4 py-6">
      <div>
        <h1 class="text-2xl font-bold">Pedidos</h1>
        <p class="text-sm text-muted-foreground">Acompanhe as compras feitas pelos clientes da sua loja.</p>
      </div>

      <div class="flex flex-wrap gap-2">
        <button v-for="item in [{ value: '', label: 'Todos' }, ...Object.entries(labels).map(([value,label]) => ({ value, label }))]" :key="item.value" class="rounded-full border px-3 py-1.5 text-sm" :class="(filters.status || '') === item.value ? 'bg-primary text-primary-foreground' : 'bg-card'" @click="filter(item.value)">{{ item.label }}</button>
      </div>

      <section class="overflow-x-auto rounded-2xl border bg-card shadow-sm">
        <table class="w-full text-sm">
          <thead class="bg-muted text-muted-foreground"><tr><th class="px-4 py-3 text-left">Pedido</th><th class="px-4 py-3 text-left">Cliente</th><th class="px-4 py-3 text-left">Itens</th><th class="px-4 py-3 text-left">Total</th><th class="px-4 py-3 text-left">Data</th><th class="px-4 py-3 text-left">Status</th></tr></thead>
          <tbody>
            <tr v-if="!orders.data.length"><td colspan="6" class="px-4 py-8 text-center text-muted-foreground">Nenhum pedido encontrado.</td></tr>
            <tr v-for="order in orders.data" :key="order.id" class="border-t align-top">
              <td class="px-4 py-3 font-semibold">{{ order.order_number || `#${order.id}` }}</td>
              <td class="px-4 py-3"><p class="font-medium">{{ order.customer?.name || order.customer_name || '-' }}</p><p class="text-xs text-muted-foreground">{{ order.customer?.whatsapp || order.customer?.email || order.contact || '-' }}</p></td>
              <td class="px-4 py-3"><p v-for="item in order.items" :key="item.id" class="text-xs">{{ item.quantity }}× {{ item.product?.name || item.product_name }}</p></td>
              <td class="px-4 py-3 font-medium">{{ formatCurrency(order.total) }}</td>
              <td class="px-4 py-3 text-muted-foreground">{{ formatDate(order.created_at) }}</td>
              <td class="px-4 py-3"><select class="rounded-lg border bg-background px-2 py-1.5 text-xs" :value="order.status" @change="(event: any) => updateStatus(order.id, event.target.value)"><option v-for="(label, value) in labels" :key="value" :value="value">{{ label }}</option></select></td>
            </tr>
          </tbody>
        </table>
      </section>

      <nav v-if="orders.links?.length > 3" class="flex flex-wrap justify-center gap-1"><Link v-for="link in orders.links" :key="link.label" :href="link.url || '#'" class="rounded border px-3 py-2 text-sm" :class="{ 'bg-primary text-primary-foreground': link.active, 'pointer-events-none opacity-40': !link.url }" preserve-scroll><span v-html="link.label"></span></Link></nav>
    </div>
  </AppLayout>
</template>
