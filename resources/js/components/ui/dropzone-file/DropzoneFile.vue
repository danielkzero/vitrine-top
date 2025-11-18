<script setup lang="ts">
import { ref, computed } from "vue";
import { UploadCloud, File as FileIcon, X } from "lucide-vue-next";

const props = defineProps({
    onCoverSelected: Function,
    displayFileTypes: { type: String, default: "" },
    titleFileTypes: { type: String, default: "" },
    allowedExtensions: { type: Array, default: () => [] },
    multiple: { type: Boolean, default: false },
    maxFiles: { type: Number, default: 1 }
});

// Armazena previews
const filesPreview = ref<{ url: string | null; file: File }[]>([]);

const acceptString = computed(() => {
    return props.allowedExtensions.map(ext => `.${ext}`).join(",");
});

function handleFiles(event: Event) {
    const input = event.target as HTMLInputElement;
    const newFiles = Array.from(input.files || []);

    for (let file of newFiles) {
        if (filesPreview.value.length >= props.maxFiles) break;

        const isImage = file.type.startsWith("image/");
        const url = isImage ? URL.createObjectURL(file) : null;

        filesPreview.value.push({ url, file });
    }

    props.onCoverSelected && props.onCoverSelected(event);
}

function removeFile(index: number) {
    filesPreview.value.splice(index, 1);
}
</script>

<template>
    <label
        for="dropzone-file"
        class="relative flex flex-col items-center justify-center w-full min-h-64
               border-2 border-dashed border-gray-300 rounded-xl cursor-pointer
               hover:bg-gray-100 dark:border-white/20 dark:hover:border-white/30
               dark:hover:bg-white/10 transition p-4"
    >
        <!-- Se houver arquivos -->
        <div v-if="filesPreview.length > 0" class="grid grid-cols-3 gap-3 w-full">

            <div
                v-for="(item, index) in filesPreview"
                :key="index"
                class="relative bg-white/80 dark:bg-white/10 p-2 rounded-lg shadow flex flex-col items-center justify-center"
            >
                <!-- Botão de remover -->
                <button
                    @click.stop.prevent="removeFile(index)"
                    class="absolute top-1 right-1 bg-black/60 text-white p-1 rounded-full"
                >
                    <X class="w-4 h-4" />
                </button>

                <!-- Se imagem, mostra thumbnail -->
                <img
                    v-if="item.url"
                    :src="item.url"
                    class="w-full h-24 object-cover rounded-md"
                />

                <!-- Se não imagem, ícone -->
                <div v-else class="flex flex-col items-center text-gray-500">
                    <FileIcon class="w-10 h-10 mb-1" />
                    <span class="text-xs text-center truncate">{{ item.file.name }}</span>
                </div>
            </div>

            <!-- Card para adicionar mais imagens -->
            <div
                v-if="filesPreview.length < maxFiles"
                class="flex flex-col items-center justify-center h-32 border-2 border-dashed rounded-lg text-gray-400"
            >
                <UploadCloud class="w-6 h-6 mb-1" />
                <p class="text-xs">Adicionar</p>
            </div>

        </div>

        <!-- Layout padrão se não tiver imagens -->
        <div v-else class="flex flex-col items-center justify-center text-center py-6">
            <UploadCloud class="w-10 h-10 mb-4 text-gray-500 dark:text-gray-400" />

            <p class="mb-2 text-sm text-gray-500 dark:text-gray-400" v-html="titleFileTypes"></p>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                {{ displayFileTypes }}
            </p>
        </div>

        <input
            id="dropzone-file"
            type="file"
            class="hidden"
            :multiple="multiple"
            :accept="acceptString"
            @change="handleFiles"
        />
    </label>
</template>
