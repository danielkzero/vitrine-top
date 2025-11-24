<!-- resources/js/components/dashboard/pages/PageHeader.vue -->
<template>
  <div class="mb-4">
    <div class="flex items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">
          {{ title }}
        </h1>
        <p v-if="subtitle" class="text-gray-500 mt-1">{{ subtitle }}</p>
      </div>

      <div v-if="$slots.actions">
        <slot name="actions" />
      </div>
    </div>

    <nav v-if="breadcrumbs?.length" class="mt-3 text-sm text-slate-500">
      <ol class="flex items-center gap-2">
        <li v-for="(b, i) in breadcrumbs" :key="i">
          <span v-if="b.href" class="hover:underline cursor-pointer" @click.prevent="$emit('goto', b.href)">{{ b.title }}</span>
          <span v-else>{{ b.title }}</span>
          <span v-if="i < breadcrumbs.length - 1" class="mx-2">/</span>
        </li>
      </ol>
    </nav>
  </div>
</template>

<script setup lang="ts">
const props = defineProps<{
  title?: string
  subtitle?: string
  breadcrumbs?: Array<{ title: string; href?: string }>
}>()

const emit = defineEmits<{
  (e: 'goto', href: string): void
}>()
</script>
