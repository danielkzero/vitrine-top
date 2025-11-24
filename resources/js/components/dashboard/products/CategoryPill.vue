<template>
  <div class="flex items-center gap-2">
    <button type="button"
      :class="[
        'px-3 py-1 rounded-lg text-sm font-medium transition whitespace-nowrap cursor-pointer',
        selected ? 'bg-sky-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
      ]"
      @click="$emit('select', category.id)"
    >
      {{ category.name }}
    </button>

    <button type="button"
      class="text-red-500 hover:text-red-700 p-1 rounded-lg cursor-pointer"
      @click.stop="$emit('delete', category)"
      title="Remover categoria"
    >
      <component :is="getIcon('Trash2')" class="w-4 h-4" />
    </button>
  </div>
</template>

<script setup lang="ts">
import { getIcon } from '@/lib/iconMap'
import type { PropType } from 'vue'

const props = defineProps({
  category: { type: Object as PropType<any>, required: true },
  selected: { type: Boolean, default: false }
})

const emit = defineEmits<{ (e:'select', id:number): void; (e:'delete', c:any): void }>()
</script>
