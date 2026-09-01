<!-- resources/js/components/vitrine/public/ProductCardSkeleton.vue -->
<template>
  <div class="animate-pulse bg-white rounded-xl p-3 shadow-sm" v-if="viewMode === 'grid'">
    <div class="h-28 mb-3 bg-slate-100 rounded-lg"></div>
    <div class="h-4 bg-slate-100 rounded w-3/4 mb-2"></div>
    <div class="flex justify-between items-center">
      <div class="h-4 bg-slate-100 rounded w-1/3"></div>
      <div class="h-4 bg-slate-100 rounded w-1/4"></div>
    </div>
  </div>
  <div class="animate-pulse bg-white rounded-xl p-3 shadow-sm flex gap-3 items-center" v-else>
    <div class="w-20 h-20 bg-slate-100 rounded-lg"></div>
    <div class="flex-1 space-y-3">
      <div class="h-4 bg-slate-100 rounded w-1/2"></div>
      <div class="h-4 bg-slate-100 rounded w-5/6"></div>
      <div class="h-4 bg-slate-100 rounded w-1/4"></div>
    </div>
  </div>
</template>
<script lang="ts" setup>
defineProps<{
    viewMode: 'grid' | 'list'
}>()
</script>
