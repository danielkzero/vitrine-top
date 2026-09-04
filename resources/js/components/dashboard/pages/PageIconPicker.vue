<template>
  <div>
    <!-- Ícone atual -->
    <button
      type="button"
      @click="open = !open"
      class="flex h-10 w-10 items-center justify-center rounded-lg border border-border transition hover:bg-accent hover:text-accent-foreground"
    >
      <component
        :is="getIcon(modelValue)"
        class="w-6 h-6 text-sky-500"
      />
    </button>

    <!-- Picker -->
    <div
      v-if="open"
      class="z-20 mt-2 grid grid-cols-3 gap-2 rounded-xl border border-border bg-popover p-3 text-popover-foreground shadow-xl md:grid-cols-6"
    >
      <button
        v-for="icon in icons"
        :key="icon.name"
        type="button"
        @click="select(icon.name)"
        class="flex flex-col items-center gap-1 rounded-xl p-2 hover:bg-accent hover:text-accent-foreground"
      >
        <component :is="getIcon(icon.name)" class="h-6 w-6" />
        <div class="mt-1 text-center text-xs text-muted-foreground">
          {{ icon.label }}
        </div>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { getIcon } from '@/lib/iconMap'

defineProps<{
  modelValue: string
  icons: Array<{ name: string; label: string }>
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
}>()

const open = ref(false)

function select(name: string) {
  emit('update:modelValue', name)
  open.value = false
}
</script>
