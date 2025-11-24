<script setup lang="ts">
import BaseButton from '@/components/ui/button/BaseButton.vue'
import { getIcon } from '@/lib/iconMap'

defineProps<{
  links: Array<any>
  removeLink: (index: number) => void
  iconLinksOptions: { label: string; value: string }[]
  addLink: () => void
  page: Record<string, any>
}>()
</script>

<template>
  <div v-if="page?.type === 'links'" class="space-y-6">

    <p class="text-slate-600 dark:text-slate-300 text-sm leading-relaxed">
      Configure aqui seus <b>links rápidos</b> que aparecerão na sua página pública.  
      Você pode definir um ícone, URL, texto e escolher se deseja exibí-lo ou não.
    </p>

    <!-- Lista de Links -->
    <div
      class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5"
    >
      <div
        v-for="(link, index) in links"
        :key="index"
        class="rounded-xl border bg-white dark:bg-slate-900/60 dark:border-slate-800 p-4 shadow-sm hover:shadow-md transition"
      >
        <!-- Header -->
        <div class="flex items-center justify-between mb-3">
          <h3 class="font-semibold text-slate-800 dark:text-white flex items-center gap-2">
            <component :is="getIcon(link.icon)" class="w-5 h-5 text-slate-600 dark:text-slate-300" />
            Link #{{ index + 1 }}
          </h3>

          <button
            class="text-red-500 hover:text-red-600 text-xs font-medium"
            @click="removeLink(index)"
          >
            Remover
          </button>
        </div>

        <!-- Campo: Ícone -->
        <div class="mb-3">
          <label class="block text-sm font-medium mb-1">Ícone</label>
          <select
            v-model="link.icon"
            class="w-full border rounded-lg px-3 py-2 dark:bg-slate-800 dark:border-slate-700 dark:text-white"
          >
            <option v-for="option in iconLinksOptions" :key="option.value" :value="option.value">
              {{ option.label }}
            </option>
          </select>
        </div>

        <!-- Campo: URL -->
        <div class="mb-3">
          <label class="block text-sm font-medium mb-1">URL</label>
          <input
            v-model="link.url"
            type="text"
            placeholder="https://..."
            class="w-full border rounded-lg px-3 py-2 dark:bg-slate-800 dark:border-slate-700 dark:text-white"
          />
        </div>

        <!-- Campo: Texto -->
        <div class="mb-3">
          <label class="block text-sm font-medium mb-1">Texto</label>
          <input
            v-model="link.text"
            type="text"
            placeholder="Nome do link (opcional)"
            class="w-full border rounded-lg px-3 py-2 dark:bg-slate-800 dark:border-slate-700 dark:text-white"
          />
        </div>

        <!-- Campo: Mostrar texto -->
        <div class="flex items-center gap-2">
          <input
            type="checkbox"
            v-model="link.showText"
            class="rounded border-gray-300 dark:border-slate-600"
          />
          <label class="text-sm text-slate-700 dark:text-slate-300">
            Mostrar texto junto ao ícone
          </label>
        </div>
      </div>
    </div>

    <!-- Botão adicionar -->
    <BaseButton
      variant="primary"
      size="md"
      leading-icon="Plus"
      @click="addLink"
    >
      Adicionar link
    </BaseButton>
  </div>
</template>
