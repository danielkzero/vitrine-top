<template>
  <div>
    <!-- Ícone atual -->
    <button
      type="button"
      @click="open = !open"
      class="flex items-center justify-center w-10 h-10 rounded-lg
             border border-slate-200 hover:bg-slate-100 transition"
    >
      <component
        :is="getIcon(modelValue)"
        class="w-6 h-6 text-sky-500"
      />
    </button>

    <!-- Picker -->
    <div
      v-if="open"
      class="mt-2 grid md:grid-cols-6 grid-cols-3 gap-2 border p-3 rounded-xl 
             bg-white dark:bg-white/10 shadow-xl z-20"
    >
      <button
        v-for="icon in icons"
        :key="icon.name"
        type="button"
        @click="select(icon.name)"
        class="p-2 hover:bg-gray-100 rounded-xl flex flex-col items-center gap-1"
      >
        <component :is="getIcon(icon.name)" class="h-6 w-6" />
        <div class="text-xs mt-1 text-gray-500 text-center">
          {{ icon.label }}
        </div>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { getIcon } from '@/lib/iconMap'

const props = defineProps<{
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
