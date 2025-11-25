<script setup>
import { getIcon } from '@/lib/iconMap'
defineProps({ reviews: Array })
</script>

<template>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    <div v-for="r in reviews" :key="r.id" class="bg-white p-4 border rounded-xl">
      <div class="flex items-center justify-between">
        <div class="font-semibold">{{ r.customer_name ?? 'Cliente' }}</div>
        <div class="text-xs text-slate-400">{{ new Date(r.created_at).toLocaleDateString('pt-BR') }}</div>
      </div>
      <div class="flex gap-1 mt-2">
        <component v-for="n in 5" :key="n" :is="getIcon(n <= r.rating ? 'Star' : 'StarOff')" class="w-4 h-4 text-yellow-400" />
      </div>
      <p class="text-sm text-slate-600 mt-2">{{ r.comment }}</p>
    </div>
  </div>
</template>
