<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { routes } from '@/config/routes';
const breadcrumbs = [
    { title: 'Início', href: routes.painel.index },
    { title: 'Categorias', href: routes.painel.categories }
];
</script>

<template>
  <Head title="Categorias" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4">
      <h1 class="text-2xl font-bold mb-4">Categorias</h1>
      <!-- Conteúdo da página -->
    </div>
  </AppLayout>
</template>
