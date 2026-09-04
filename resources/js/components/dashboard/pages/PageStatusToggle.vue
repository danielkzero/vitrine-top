<template>
  <button
    type="button"
    @click="$emit('update:modelValue', !modelValue)"
    class="inline-flex items-center justify-center text-sm font-medium rounded-xl px-3 py-2
           transition border"
    :class="modelValue
      ? 'border-emerald-200 bg-emerald-100 text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-300'
      : 'border-border bg-muted text-muted-foreground'"
  >
    <component
      :is="modelValue ? getIcon('Eye') : getIcon('EyeOff')"
      class="w-5 h-5 mr-1"
    />

    {{ modelValue ? 'Ativa' : 'Inativa' }}
  </button>
</template>

<script setup lang="ts">
import { getIcon } from '@/lib/iconMap'

defineProps<{
  modelValue: boolean
}>()

defineEmits<{
  (e: 'update:modelValue', value: boolean): void
}>()
</script>
