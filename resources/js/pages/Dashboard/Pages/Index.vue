<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, usePage } from '@inertiajs/vue3'
import PageCard from '@/components/dashboard/PageCard.vue'
import PageCardSkeleton from '@/components/dashboard/PageCardSkeleton.vue'
import { routes } from '@/config/routes'

const { props } = usePage()
const pages = props.pages?.data || []

const loading = pages.length === 0 // ← comportamento padrão

const Breadcrumbs = [
  { title: 'Painel', href: routes.painel.index },
  { title: 'Páginas', href: '#' }
]
</script>


<template>

  <Head title="Gerenciamento de Páginas" />

  <AppLayout :breadcrumbs="Breadcrumbs">
    <main class="flex flex-col gap-6 p-6 px-4 container mx-auto">

      <div>
        <h1 class="text-3xl font-bold text-gray-900">Gerenciamento de Páginas</h1>
        <p class="text-gray-500">
          Abaixo estão todas as páginas do seu painel.
        </p>
      </div>

      <!-- Lista de páginas -->
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">

        <!-- Skeleton enquanto carrega -->
        <PageCardSkeleton v-if="loading" v-for="n in 6" :key="'skeleton-' + n" />

        <!-- Cards reais -->
        <PageCard v-else v-for="page in pages" :key="page.id" :title="page.title" :description="page.seo_description"
          :icon="page.icon" :type="page.type" :active="page.is_active" :editHref="`/painel/pages/edit/${page.key}`" />
      </div>

      <!-- Sem páginas -->
      <div v-if="!loading && pages.length === 0" class="text-center text-gray-500 mt-10">
        Nenhuma página encontrada.
      </div>


    </main>
  </AppLayout>
</template>
