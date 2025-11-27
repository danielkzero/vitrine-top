<script setup>
import { getIcon } from '@/lib/iconMap'
defineProps({ page: Object })
</script>

<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
    <a v-for="(link, idx) in page.content" :key="idx"
       :href="link.url" target="_blank" rel="noopener noreferrer"
       class="flex items-center gap-3 p-4 bg-white border rounded-xl hover:shadow transition">
      <component :is="getIcon(link.icon || 'Share2')" class="w-6 h-6 text-emerald-500" />
      <div>
        <div class="font-medium" v-if="link.text">{{ link.text }}</div>
        <div class="text-xs text-slate-400">{{ link.url }}</div>
      </div>
    </a>
  </div>
</template>
