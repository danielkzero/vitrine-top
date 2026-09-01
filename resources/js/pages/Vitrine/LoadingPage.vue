<script setup lang="ts">
defineProps<{
  loading: boolean
}>()
</script>

<template>
  <div v-if="loading" class="flex flex-col items-center justify-center min-h-screen py-20">
    <div class="relative w-16 h-16">
      <div class="absolute inset-0 rounded-full border-4 border-slate-300/40"></div>
      <div class="absolute inset-0 rounded-full border-4 border-transparent border-t-slate-700 animate-spin"></div>
    </div>

    <p class="mt-5 text-sm font-medium text-slate-500 animate-pulse">
      Carregando conteudo...
    </p>
  </div>
</template>
