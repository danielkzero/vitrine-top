<script setup lang="ts">
import { UploadCloud } from 'lucide-vue-next'
defineProps<{
    page: {};
    removeGalleryImage: (index: number) => void;
    LucideIcons: typeof LucideIcons;
    onGallerySelected: (event: Event) => void;
}>()
</script>

<template>
    <div v-if="page.type === 'gallery'" class="space-y-4">
        <p class="text-gray-600 text-sm mb-2">
            Aqui você pode gerenciar as <b>fotos da galeria</b> da página.
            Você pode enviar novas imagens, visualizar as existentes e remover as que não
            desejar mais exibir.
        </p>

        <!-- Lista de imagens -->
        <div v-if="Array.isArray(page.content) && page.content.length"
            class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            <div v-for="(image, index) in page.content" :key="index"
                class="relative group border rounded-xl overflow-hidden shadow hover:shadow-md transition">
                <img :src="image" alt="Imagem da galeria" class="object-cover w-full h-32" />
                <button type="button" @click="removeGalleryImage(index)"
                    class="absolute top-1 right-1 bg-red-600 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition">
                    <component :is="LucideIcons.Trash" class="w-4 h-4" />
                </button>
            </div>
        </div>

        <!-- Dropzone para adicionar novas imagens -->
        <label for="gallery-dropzone" class="flex flex-col items-center justify-center 
	                                    w-full h-64 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer 
	                                    bg-gray-50 hover:bg-gray-100 transition">
            <div class="flex flex-col items-center justify-center pt-5 pb-6 text-center">
                <UploadCloud class="w-10 h-10 mb-4 text-gray-500" />
                <p class="mb-2 text-sm text-gray-500">
                    <span class="font-semibold">Clique para enviar</span> ou arraste e solte
                    imagens
                </p>
                <p class="text-xs text-gray-400">
                    JPG, PNG, WEBP (MAX. 1MB por imagem)
                </p>
            </div>
            <input id="gallery-dropzone" type="file" class="hidden" accept="image/*" multiple
                @change="onGallerySelected" />
        </label>
    </div>
</template>
