<!-- resources/js/components/vitrine/public/BottomNav.vue -->
<script setup lang="ts">
import { getIcon } from '@/lib/iconMap'
import { computed } from 'vue'

const props = defineProps({
  user: Object,
  pages: { type: Array, default: () => [] },
  activeKey: { type: String, default: null }
})

const emit = defineEmits(['navigate'])

const sortedPages = computed(() => {
  return [...props.pages]
    .filter(p => p.is_active)
    .sort((a, b) => a.order - b.order)
})

function go(page) {
  emit('navigate', page)
}
</script>

<template>
  <nav class="fixed bottom-4 left-2 right-2 z-40">
    <div class="bg-white border-2 border-slate-100 rounded-2xl px-6 py-3 flex justify-between items-center shadow-xl mx-auto max-w-xl">

      <button
        v-for="page in sortedPages"
        :key="page.key"
        @click="go(page)"
        class="flex flex-col items-center justify-center transition-all"
        :class="props.activeKey === page.key ? '' : 'text-slate-500'"
        :style="props.activeKey === page.key ? { color: props.user.theme_color } : ''"
      >
        <component :is="getIcon(page.icon || 'Home')" class="w-6 h-6 mb-1" />
        <span class="text-[10px] font-medium truncate max-w-[60px]">{{ page.title }}</span>
        <div :class="'w-1.5 h-1.5 rounded-full mt-1'"
          :style="{ backgroundColor: props.user.theme_color }"
          v-if="props.activeKey === page.key"></div>
      </button>

    </div>
  </nav>
</template>
