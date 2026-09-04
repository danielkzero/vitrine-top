<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { routes } from '@/config/routes'
import { formatCurrency, formatDate } from '@/lib/utils'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps<{ customers: any; filters: { search?: string } }>()
const search = ref(props.filters.search || '')
const breadcrumbs = [{ title: 'Painel', href: routes.painel.index }, { title: 'Clientes', href: routes.painel.customers.index }]

function submitSearch() {
  router.get(routes.painel.customers.index, search.value ? { search: search.value } : {}, { preserveState: true, replace: true })
}
</script>

<template>
  <Head title="Clientes" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="container mx-auto space-y-6 px-4 py-6">
      <div>
        <h1 class="text-2xl font-bold">Clientes</h1>
        <p class="text-sm text-muted-foreground">Clientes cadastrados exclusivamente na sua loja.</p>
      </div>

      <form class="flex max-w-xl gap-2" @submit.prevent="submitSearch"><input v-model="search" class="min-w-0 flex-1 rounded-lg border bg-background px-3 py-2 text-sm" placeholder="Buscar por nome, e-mail ou WhatsApp"><button class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground">Buscar</button></form>

      <section class="overflow-x-auto rounded-2xl border bg-card shadow-sm">
        <table class="w-full text-sm">
          <thead class="bg-muted text-muted-foreground"><tr><th class="px-4 py-3 text-left">Cliente</th><th class="px-4 py-3 text-left">Contato</th><th class="px-4 py-3 text-left">Endereço principal</th><th class="px-4 py-3 text-left">Pedidos</th><th class="px-4 py-3 text-left">Total comprado</th><th class="px-4 py-3 text-left">Cadastro</th></tr></thead>
          <tbody>
            <tr v-if="!customers.data.length"><td colspan="6" class="px-4 py-8 text-center text-muted-foreground">Nenhum cliente encontrado.</td></tr>
            <tr v-for="customer in customers.data" :key="customer.id" class="border-t align-top">
              <td class="px-4 py-3"><p class="font-semibold">{{ customer.name }}</p><span class="text-xs" :class="customer.is_active ? 'text-emerald-600' : 'text-rose-600'">{{ customer.is_active ? 'Ativo' : 'Inativo' }}</span></td>
              <td class="px-4 py-3"><p>{{ customer.email }}</p><p class="text-xs text-muted-foreground">{{ customer.whatsapp || '-' }}</p></td>
              <td class="max-w-xs px-4 py-3 text-muted-foreground"><template v-if="customer.default_address">{{ customer.default_address.street }}, {{ customer.default_address.number }}<br>{{ customer.default_address.neighborhood }} — {{ customer.default_address.city }}/{{ customer.default_address.state }}</template><span v-else>Não informado</span></td>
              <td class="px-4 py-3 font-medium">{{ customer.orders_count }}</td>
              <td class="px-4 py-3 font-medium">{{ formatCurrency(customer.orders_total || 0) }}</td>
              <td class="px-4 py-3 text-muted-foreground">{{ formatDate(customer.created_at) }}</td>
            </tr>
          </tbody>
        </table>
      </section>

      <nav v-if="customers.links?.length > 3" class="flex flex-wrap justify-center gap-1"><Link v-for="link in customers.links" :key="link.label" :href="link.url || '#'" class="rounded border px-3 py-2 text-sm" :class="{ 'bg-primary text-primary-foreground': link.active, 'pointer-events-none opacity-40': !link.url }" preserve-scroll><span v-html="link.label"></span></Link></nav>
    </div>
  </AppLayout>
</template>
