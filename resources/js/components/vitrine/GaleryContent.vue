<script setup lang="ts">
import DropzoneFile from '@/components/ui/dropzone-file/DropzoneFile.vue'
import BaseButton from '@/components/ui/button/BaseButton.vue'
import { getIcon } from '@/lib/iconMap'

defineProps<{
  page: any
  removeGalleryImage: (index: number) => void
  onGallerySelected: (event: Event | File[] | FileList) => void
}>()
</script>

<template>
  <div v-if="page.type === 'gallery'" class="space-y-6">

    <!-- Descrição -->
    <p class="text-slate-600 dark:text-slate-300 text-sm leading-relaxed">
      Gerencie as <b>imagens da galeria</b> desta página.  
      Você pode enviar novas imagens, visualizar as existentes e removê-las facilmente.
    </p>

    <!-- Galeria -->
    <div
      v-if="Array.isArray(page.content) && page.content.length"
      class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4"
    >
      <div
        v-for="(image, index) in page.content"
        :key="index"
        class="relative group rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm hover:shadow-md transition"
      >
        <img
          :src="image"
          alt="Imagem da galeria"
          class="w-full h-32 object-cover"
        />

        <!-- Botão Remover -->
        <button
          type="button"
          @click="removeGalleryImage(index)"
          class="absolute top-2 right-2 bg-red-600 hover:bg-red-700 text-white rounded-full p-1 shadow opacity-0 group-hover:opacity-100 transition"
        >
          <component :is="getIcon('Trash')" class="w-4 h-4" />
        </button>
      </div>
    </div>

    <!-- Placeholder quando vazio -->
    <div
      v-else
      class="w-full p-6 text-center border border-dashed border-slate-300 dark:border-slate-700 rounded-xl text-slate-500 dark:text-slate-400 text-sm"
    >
      Nenhuma imagem enviada ainda. Adicione imagens abaixo.
    </div>

    <!-- Dropzone -->
    <DropzoneFile
      :onCoverSelected="onGallerySelected"
      titleFileTypes="<span class='font-semibold'>Clique para enviar</span> ou arraste e solte"
      displayFileTypes="JPG, PNG, WEBP (MAX. 1MB por imagem)"
      :multiple="true"
      :maxFiles="10"
    />
  </div>
</template>
