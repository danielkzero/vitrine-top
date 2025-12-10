<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import BaseButton from '@/components/ui/button/BaseButton.vue';
import { getIcon } from '@/lib/iconMap';

import { route } from 'ziggy-js';

const props = defineProps<{
  stats: {
    products: { imagesCount: number; total: number }
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
    href: route('painel.index'),
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
        <div class="md:flex md:justify-start gap-3 space-y-3">
          <!-- Titulo -->
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
              Painel
            </h1>

            <p class="text-gray-500 dark:text-gray-200">Visão geral da sua loja</p>
          </div>
          <div class="ms-auto gap-3 flex">
            <!-- Botão Configurar Loja -->
            <BaseButton as="Link" :href="['/settings/store']" variant="primary" size="lg" leading-icon="Settings">
              Configurar Loja
            </BaseButton>

            <!-- Botão Ver Vitrine (condicional) -->
            <BaseButton v-if="props.user?.business_name && props.user.slug" as="a" :href="[`/${props.user.slug}`]"
              variant="secondary" size="lg" leading-icon="StoreIcon" target="_blank">
              Ver Vitrine
            </BaseButton>
          </div>
        </div>
      </div>

      <!-- Cards de Métricas -->
      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">

        <!-- Produtos -->
        <div class="relative rounded-xl bg-white p-6 border border-slate-100 text-sky-700 shadow-lg 
            after:absolute after:left-0 after:top-0 after:h-full after:w-3 after:bg-sky-400 after:rounded-l-xl">
          <div
            class="absolute top-4 right-4 w-12 h-12 rounded-full bg-gradient-to-tr from-sky-400 to-sky-700 flex items-center justify-center text-white shadow">
            <component :is="getIcon('Package')" class="w-6 h-6" />
          </div>
          <p class="font-semibold">Produtos Ativos</p>
          <h2 class="text-3xl font-bold mt-2">{{ props.stats?.products.total }}</h2>
        </div>

        <!-- Pedidos -->
        <div class="relative rounded-xl bg-white p-6 border border-slate-100 text-amber-700 shadow-lg
            after:absolute after:left-0 after:top-0 after:h-full after:w-3 after:bg-amber-400 after:rounded-l-xl">
          <div
            class="absolute top-4 right-4 w-12 h-12 rounded-full bg-gradient-to-tr from-amber-400 to-amber-700 flex items-center justify-center text-white shadow">
            <component :is="getIcon('Image')" class="w-6 h-6" />
          </div>
          <p class="font-semibold">Fotos de produtos</p>
          <h2 class="text-3xl font-bold mt-2">{{ props.stats?.products.imagesCount }}</h2>
        </div>

        <!-- Receita -->
        <div class="relative rounded-xl bg-white p-6 border border-slate-100 text-emerald-700 shadow-lg
            after:absolute after:left-0 after:top-0 after:h-full after:w-3 after:bg-emerald-400 after:rounded-l-xl">
          <div
            class="absolute top-4 right-4 w-12 h-12 rounded-full bg-gradient-to-tr from-emerald-400 to-emerald-700 flex items-center justify-center text-white shadow">
            <component :is="getIcon('Image')" class="w-6 h-6" />
          </div>
          <p class="font-semibold">Fotos galeria</p>
          <h2 class="text-3xl font-bold mt-2">
            {{ props.stats?.revenue.total }}
          </h2>
        </div>
      </div>

      <!-- Seções inferiores -->
      <div class="grid gap-6 lg:grid-cols-1" v-if="true == false">

        <!-- Pedidos Recentes -->
        <div class="rounded-2xl border border-slate-100 bg-white p-0 shadow-lg">
          <div class="p-4 border-b border-slate-100">
            <h3 class="font-semibold text-slate-800">Pedidos Recentes</h3>
          </div>
          <div class="divide-y divide-slate-100">
            <div v-if="!props?.recentOrders?.length" class="p-4 text-slate-500">
              Nenhum pedido recente encontrado.
            </div>
            <div v-for="order in props?.recentOrders" :key="order.id" class="flex justify-between p-4 text-slate-700">
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
