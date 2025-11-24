<template>
  <div class="md:border p-4 rounded-xl space-y-4 md:shadow">
    <!-- Header -->
    <h2
      class="items-center text-2xl font-extrabold flex gap-2 p-3 border bg-gray-100/50 dark:bg-white/10 rounded-xl"
    >
      <component :is="getIcon('SquareCheckBig')" class="h-6 w-6 text-sky-400" />
      <span
        class="text-transparent bg-clip-text bg-gradient-to-r to-emerald-600 from-sky-400"
      >
        Conteúdo da página
      </span>
    </h2>

    <!-- Título, Ícone e Status -->
    <div>
      <label class="block text-sm font-medium mb-1">Título e ícone</label>

      <div class="flex items-center gap-3">
        <!-- Ícone atual -->
        <component
          v-if="page.icon"
          :is="getIcon(page.icon)"
          class="h-6 w-6 text-sky-500 cursor-pointer hover:scale-110"
          @click="toggleIconPicker"
        />

        <!-- Título -->
        <input
          v-model="page.title"
          type="text"
          placeholder="Título da página"
          class="flex-1 border rounded-xl px-3 py-2"
        />

        <!-- Toggle de status -->
        <PageStatusToggle v-model="page.is_active" />
      </div>

      <!-- Icon Picker -->
      <div
        v-if="showIconPicker"
        class="mt-2 grid md:grid-cols-6 grid-cols-3 gap-2 border p-3 rounded-xl bg-white dark:bg-white/10 shadow-lg"
      >
        <button
          v-for="opt in iconOptions"
          :key="opt.name"
          type="button"
          @click="selectIcon(opt.name)"
          class="p-2 hover:bg-gray-100 rounded-xl text-center"
        >
          <component :is="getIcon(opt.name)" class="h-6 w-6 mx-auto" />
          <div class="text-xs mt-1 text-gray-500 text-center">
            {{ opt.label }}
          </div>
        </button>
      </div>
    </div>

    <!-- Conteúdos dinâmicos -->
    <slot />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import PageStatusToggle from '@/components/dashboard/pages/PageStatusToggle.vue'
import { getIcon } from '@/lib/iconMap'

const props = defineProps<{
  page: any
  iconOptions: Array<{ name: string; label: string }>
}>()

const emit = defineEmits(['update:page'])

const page = props.page
const iconOptions = props.iconOptions

const showIconPicker = ref(false)

function toggleIconPicker() {
  showIconPicker.value = !showIconPicker.value
}

function selectIcon(icon: string) {
  page.icon = icon
  showIconPicker.value = false
}
</script>
