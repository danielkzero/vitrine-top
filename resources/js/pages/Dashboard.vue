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
    <div class="container mx-auto px-6 py-6 flex flex-col gap-6">
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
            class="px-4 py-2 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white rounded-lg text-sm flex items-center gap-2 ms-auto shadow-lg">
            <Settings class="w-4 h-4" />
            Configurar Loja
          </Link>

          <!-- Botão Ver Vitrine (condicional) -->
          <Link v-if="props.user.business_name && props.user.slug" :href="`/vitrine/${props.user.slug}`"
            class="px-4 py-2 bg-white border border-slate-100 text-slate-700 rounded-lg text-sm flex items-center gap-2">
            <ExternalLink class="w-4 h-4" />
            Ver Vitrine
          </Link>

        </div>
        <p class="text-gray-500 dark:text-gray-200">Visão geral da sua loja</p>
      </div>

      <!-- Cards de Métricas -->
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">

        <!-- Produtos -->
        <div class="relative rounded-xl bg-white p-6 border border-slate-100 text-slate-800 shadow-lg">
          <div class="absolute top-4 right-4 w-12 h-12 rounded-full bg-gradient-to-tr from-sky-400 to-emerald-500 flex items-center justify-center text-white shadow">
            <Package class="w-6 h-6" />
          </div>
          <p class="font-semibold">Produtos Ativos</p>
          <h2 class="text-3xl font-bold mt-2">{{ props.stats.products.active }}</h2>
          <p class="text-sm mt-1 text-slate-500">{{ props.stats.products.total }} total</p>
        </div>

        <!-- Pedidos -->
        <div class="relative rounded-xl bg-white p-6 border border-slate-100 text-slate-800 shadow-lg">
          <div class="absolute top-4 right-4 w-12 h-12 rounded-full bg-gradient-to-tr from-sky-400 to-emerald-500 flex items-center justify-center text-white shadow">
            <ShoppingCart class="w-6 h-6" />
          </div>
          <p class="font-semibold">Pedidos Pendentes</p>
          <h2 class="text-3xl font-bold mt-2">{{ props.stats.orders.pending }}</h2>
          <p class="text-sm mt-1 text-slate-500">{{ props.stats.orders.pending }} total</p>
        </div>

        <!-- Receita -->
        <div class="relative rounded-xl bg-white p-6 border border-slate-100 text-slate-800 shadow-lg">
          <div class="absolute top-4 right-4 w-12 h-12 rounded-full bg-gradient-to-tr from-sky-400 to-emerald-500 flex items-center justify-center text-white shadow">
            <DollarSign class="w-6 h-6" />
          </div>
          <p class="font-semibold">Receita Total</p>
          <h2 class="text-3xl font-bold mt-2">
            R$ {{ props.stats.revenue.total }}
          </h2>
          <p class="text-sm mt-1 text-slate-500">De {{ props.stats.revenue.count }} pedidos</p>
        </div>
      </div>

      <!-- Seções inferiores -->
      <div class="grid gap-6 lg:grid-cols-2">
        <!-- Ações rápidas -->
        <div class="rounded-2xl border border-slate-100 bg-white p-0 shadow-lg">
          <div class="p-4 border-b border-slate-100">
            <h3 class="font-semibold text-slate-800">Ações Rápidas</h3>
          </div>
          <div class="divide-y divide-slate-100">
            <button class="flex items-center gap-2 w-full p-4 hover:bg-slate-50 cursor-pointer">
              <Package class="w-5 h-5 text-slate-700" />
              <span class="text-slate-700">Gerenciar Produtos</span>
            </button>

            <button class="flex items-center gap-2 w-full p-4 hover:bg-slate-50 cursor-pointer">
              <Receipt class="w-5 h-5 text-slate-700" />
              <span class="text-slate-700">Ver Pedidos</span>
            </button>

            <button class="flex items-center gap-2 w-full p-4 hover:bg-slate-50 cursor-pointer rounded-b-xl">
              <Settings class="w-5 h-5 text-slate-700" />
              <span class="text-slate-700">Configurar Loja</span>
            </button>
          </div>
        </div>

        <!-- Pedidos Recentes -->
        <div class="rounded-2xl border border-slate-100 bg-white p-0 shadow-lg">
          <div class="p-4 border-b border-slate-100">
            <h3 class="font-semibold text-slate-800">Pedidos Recentes</h3>
          </div>
          <div class="divide-y divide-slate-100">
            <div v-if="!props.recentOrders.length" class="p-4 text-slate-500">
              Nenhum pedido recente encontrado.
            </div>
            <div v-for="order in props.recentOrders" :key="order.id"
              class="flex justify-between p-4 text-slate-700">
              <div>
                <p class="font-semibold">{{ order.user?.name ?? 'Cliente' }}</p>
                <p class="text-sm text-slate-500">{{ order.status }}</p>
              </div>
              <p class="font-semibold">R$ {{ order.total.toFixed(2) }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
