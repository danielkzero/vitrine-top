<template>
  <div class="border-b border-gray-200 dark:border-white/10 px-4 md:px-0">
    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
      <button type="button"
        v-for="tab in tabs"
        :key="tab.name"
        @click="$emit('update:modelValue', tab.name)"
        :class="modelValue === tab.name
          ? 'border-primary text-primary dark:text-primary font-semibold'
          : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-white'"
        class="cursor-pointer whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none"
      >
        {{ tab.label }}
      </button>
    </nav>
  </div>
</template>

<script setup lang="ts">
interface TabItem {
  name: string
  label: string
}

const props = defineProps<{
  modelValue: string
  tabs: TabItem[]
}>()

defineEmits<{
  (e: 'update:modelValue', value: string): void
}>()
</script>
