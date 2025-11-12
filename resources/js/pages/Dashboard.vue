<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const props = defineProps<{
  stats: {
    products: { active: number; total: number }
    orders: { pending: number }
    revenue: { total: number; count: number }
  }
  recentOrders: Array<{
    id: number
    total: number
    status: string
    user: { name: string }
  }>
}>()

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Painel',
        href: dashboard().url,
    },
];
</script>

<template>
  <Head title="Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-6 p-6 container mx-auto">
      <!-- Cabeçalho -->
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
          Dashboard
        </h1>
        <p class="text-gray-500">Visão geral da sua loja</p>
      </div>

      <!-- Cards de Métricas -->
      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <!-- Produtos -->
        <div class="rounded-xl bg-blue-600 p-6 text-white shadow">
          <p class="font-semibold">Produtos Ativos</p>
          <h2 class="text-3xl font-bold mt-2">{{ props.stats.products.active }}</h2>
          <p class="text-sm mt-1">{{ props.stats.products?.total }} total</p>
        </div>

        <!-- Pedidos -->
        <div class="rounded-xl bg-orange-500 p-6 text-white shadow">
          <p class="font-semibold">Pedidos Pendentes</p>
          <h2 class="text-3xl font-bold mt-2">{{ props.stats.orders.pending }}</h2>
          <p class="text-sm mt-1">{{ props.stats.orders.pending }} total</p>
        </div>

        <!-- Receita -->
        <div class="rounded-xl bg-green-600 p-6 text-white shadow">
          <p class="font-semibold">Receita Total</p>
          <h2 class="text-3xl font-bold mt-2">
            R$ {{ props.stats.revenue?.total }}
          </h2>
          <p class="text-sm mt-1">De {{ props.stats.revenue.count }} pedidos</p>
        </div>
      </div>

      <!-- Seções inferiores -->
      <div class="grid gap-6 lg:grid-cols-2">
        <!-- Ações rápidas -->
        <div class="rounded-xl border border-gray-200 bg-white dark:bg-gray-800 shadow-sm">
          <div class="p-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="font-semibold text-gray-800 dark:text-gray-100">Ações Rápidas</h3>
          </div>
          <div class="divide-y divide-gray-100 dark:divide-gray-700">
            <button class="flex items-center gap-2 w-full p-4 hover:bg-gray-50 dark:hover:bg-gray-700">
              🧱 <span>Gerenciar Produtos</span>
            </button>
            <button class="flex items-center gap-2 w-full p-4 hover:bg-gray-50 dark:hover:bg-gray-700">
              🧾 <span>Ver Pedidos</span>
            </button>
            <button class="flex items-center gap-2 w-full p-4 hover:bg-gray-50 dark:hover:bg-gray-700">
              ⚙️ <span>Configurar Loja</span>
            </button>
          </div>
        </div>

        <!-- Pedidos Recentes -->
        <div class="rounded-xl border border-gray-200 bg-white dark:bg-gray-800 shadow-sm">
          <div class="p-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="font-semibold text-gray-800 dark:text-gray-100">Pedidos Recentes</h3>
          </div>
          <div class="divide-y divide-gray-100 dark:divide-gray-700">
            <div v-if="!props.recentOrders.length" class="p-4 text-gray-500">
                Nenhum pedido recente encontrado.
            </div>
            <div
              v-for="order in props.recentOrders"
              :key="order.id"
              class="flex justify-between p-4 text-gray-700 dark:text-gray-200"
            >
              <div>
                <p class="font-semibold">{{ order.user?.name ?? 'Cliente' }}</p>
                <p class="text-sm text-gray-500">{{ order.status }}</p>
              </div>
              <p class="font-semibold">R$ {{ order.total.toFixed(2) }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
