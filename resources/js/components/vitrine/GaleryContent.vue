<script setup lang="ts">
import { UploadCloud } from 'lucide-vue-next';
import DropzoneFile from '@/components/ui/dropzone-file/DropzoneFile.vue';
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
        <DropzoneFile :onCoverSelected="onGallerySelected"
            titleFileTypes="<span class='font-semibold'>Clique para enviar</span> ou arraste e solte"
            displayFileTypes="JPG, PNG, WEBP (MAX. 1MB por imagem)" />
    </div>
</template>
