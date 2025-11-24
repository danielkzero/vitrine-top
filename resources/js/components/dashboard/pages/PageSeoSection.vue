<template>
  <div class="md:border p-4 rounded-xl space-y-4 md:shadow">
    <!-- Header -->
    <h2
      class="text-2xl font-extrabold flex gap-2 p-3 border bg-gray-100/50 dark:bg-white/10 rounded-xl"
    >
      <component :is="getIcon('SquareCheckBig')" class="h-6 w-6 text-sky-400" />
      <span
        class="text-transparent bg-clip-text bg-gradient-to-r to-emerald-600 from-sky-400"
      >
        SEO
      </span>
    </h2>

    <!-- SEO Title -->
    <div>
      <label class="block text-sm font-medium mb-1">Título SEO</label>
      <input
        v-model="page.seo_title"
        type="text"
        class="w-full border rounded-xl px-3 py-2"
        placeholder="Título exibido no Google"
      />
    </div>

    <!-- SEO Description -->
    <div>
      <label class="block text-sm font-medium mb-1">Descrição SEO</label>
      <textarea
        v-model="page.seo_description"
        class="w-full border rounded-xl px-3 py-2 h-24"
        placeholder="Descrição que aparece nos resultados do Google"
      ></textarea>
    </div>

    <!-- Cover Image -->
    <div>
      <label class="block text-sm font-medium mb-1">Imagem de Capa</label>

      <DropzoneFile
        :onCoverSelected="onCoverSelected"
        titleFileTypes="<span class='font-semibold'>Clique para enviar</span> ou arraste e solte"
        displayFileTypes="SVG, PNG, JPG ou GIF (MAX. 800x400px)"
      />
    </div>

    <!-- Preview -->
    <div v-if="coverPreview" class="mt-4 flex justify-center">
      <img
        :src="coverPreview"
        alt="Prévia da capa"
        class="max-h-48 rounded-xl shadow border object-cover"
      />
    </div>

    <slot />
  </div>
</template>

<script setup lang="ts">
import DropzoneFile from '@/components/ui/dropzone-file/DropzoneFile.vue'
import { getIcon } from '@/lib/iconMap'

const props = defineProps<{
  page: any
  coverPreview: string | null
  onCoverSelected: (event: Event) => void
}>()

const page = props.page
const coverPreview = props.coverPreview
const onCoverSelected = props.onCoverSelected
</script>
