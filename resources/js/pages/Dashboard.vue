<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

// Ícones
import {
  Package,
  Receipt,
  Settings,
  ShoppingCart,
  DollarSign,
  ExternalLink
} from "lucide-vue-next";

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
  user: {
    business_name?: string
    slug?: string
  }
}>()

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Painel',
    href: dashboard().url,
  },
];
</script>


<template>

  <Head title="Painel" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-6 p-4 md:p-6 container mx-auto">
      <!-- Cabeçalho -->
      <div>
        <!-- Botões superiores -->
        <div class="flex justify-start gap-3">
          <!-- Titulo -->
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
              Painel
            </h1>

          </div>

          <!-- Botão Configurar Loja -->
          <Link href="/settings/store"
            class="px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white rounded-lg text-sm flex items-center gap-2 ms-auto">
            <Settings class="w-4 h-4" />
            Configurar Loja
          </Link>

          <!-- Botão Ver Vitrine (condicional) -->
          <Link v-if="props.user.business_name && props.user.slug" :href="`/vitrine/${props.user.slug}`"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm flex items-center gap-2">
            <ExternalLink class="w-4 h-4" />
            Ver Vitrine
          </Link>

        </div>
        <p class="text-gray-500">Visão geral da sua loja</p>
      </div>

      <!-- Cards de Métricas -->
      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">

        <!-- Produtos -->
        <div class="relative rounded-xl bg-blue-600 dark:bg-blue-200 dark:text-gray-800 p-6 text-white shadow">
          <Package class="absolute top-4 right-4 w-8 h-8 opacity-80" />
          <p class="font-semibold">Produtos Ativos</p>
          <h2 class="text-3xl font-bold mt-2">{{ props.stats.products.active }}</h2>
          <p class="text-sm mt-1">{{ props.stats.products.total }} total</p>
        </div>

        <!-- Pedidos -->
        <div class="relative rounded-xl bg-orange-500 dark:bg-orange-200 dark:text-gray-800 p-6 text-white shadow">
          <ShoppingCart class="absolute top-4 right-4 w-8 h-8 opacity-80" />
          <p class="font-semibold">Pedidos Pendentes</p>
          <h2 class="text-3xl font-bold mt-2">{{ props.stats.orders.pending }}</h2>
          <p class="text-sm mt-1">{{ props.stats.orders.pending }} total</p>
        </div>

        <!-- Receita -->
        <div class="relative rounded-xl bg-green-600 dark:bg-green-200 dark:text-gray-800 p-6 text-white shadow">
          <DollarSign class="absolute top-4 right-4 w-8 h-8 opacity-80" />
          <p class="font-semibold">Receita Total</p>
          <h2 class="text-3xl font-bold mt-2">
            R$ {{ props.stats.revenue.total }}
          </h2>
          <p class="text-sm mt-1">De {{ props.stats.revenue.count }} pedidos</p>
        </div>
      </div>

      <!-- Seções inferiores -->
      <div class="grid gap-6 lg:grid-cols-2">
        <!-- Ações rápidas -->
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
          <div class="p-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="font-semibold text-gray-800 dark:text-gray-100">Ações Rápidas</h3>
          </div>
          <div class="divide-y divide-gray-100 dark:divide-gray-700">
            <button class="flex items-center gap-2 w-full p-4 hover:bg-gray-50 dark:hover:bg-gray-700">
              <Package class="w-5 h-5" />
              <span>Gerenciar Produtos</span>
            </button>

            <button class="flex items-center gap-2 w-full p-4 hover:bg-gray-50 dark:hover:bg-gray-700">
              <Receipt class="w-5 h-5" />
              <span>Ver Pedidos</span>
            </button>

            <button class="flex items-center gap-2 w-full p-4 hover:bg-gray-50 dark:hover:bg-gray-700">
              <Settings class="w-5 h-5" />
              <span>Configurar Loja</span>
            </button>
          </div>
        </div>

        <!-- Pedidos Recentes -->
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
          <div class="p-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="font-semibold text-gray-800 dark:text-gray-100">Pedidos Recentes</h3>
          </div>
          <div class="divide-y divide-gray-100 dark:divide-gray-700">
            <div v-if="!props.recentOrders.length" class="p-4 text-gray-500">
              Nenhum pedido recente encontrado.
            </div>
            <div v-for="order in props.recentOrders" :key="order.id"
              class="flex justify-between p-4 text-gray-700 dark:text-gray-200">
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
