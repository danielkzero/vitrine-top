<script setup lang="ts">
import { getIcon } from '@/lib/iconMap'
const props = defineProps<{
  user: Record<string, any>
  page: Record<string, any>
}>()
</script>

<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <a v-for="(link, idx) in JSON.parse(page.content)" :key="idx" :href="link.url" target="_blank" rel="noopener noreferrer"
      class="flex items-center gap-3 p-4 bg-white border rounded-xl hover:shadow transition md:rounded-none md:border-0 md:bg-transparent md:shadow-none">
      <component :is="getIcon(link.icon || 'Share2')" class="w-6 h-6"
      :style="{ color: props.user.theme_color }" />
      <div>
        <div class="font-medium" v-if="link.text">{{ link.text }}</div>
        <div class="text-xs text-slate-400">{{ link.url }}</div>
      </div>
    </a>
  </div>
</template>
