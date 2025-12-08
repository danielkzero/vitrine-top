<script setup lang="ts">
import { ref, computed, onMounted, markRaw } from "vue";
import { getIcon } from "@/lib/iconMap";

const emit = defineEmits(["onCoverSelected"]);

const props = defineProps({
    displayFileTypes: { type: String, default: "" },
    titleFileTypes: { type: String, default: "" },
    allowedExtensions: { type: Array, default: () => [] },
    multiple: { type: Boolean, default: false },
    maxFiles: { type: Number, default: 3 },
    initialFiles: { type: Array, default: () => [] },
});

// files internos
const filesPreview = ref<
    { id?: number; url: string | null; file: File | null; isOld?: boolean }[]
>([]);

const fileInput = ref<HTMLInputElement | null>(null);
const errorMessage = ref("");

// tipos aceitos
const acceptString = computed(() =>
    props.allowedExtensions.map((ext) => `.${ext}`).join(",")
);

/* =====================================
   CARREGAR ARQUIVOS EXISTENTES (URL)
===================================== */
onMounted(() => {
    props.initialFiles.forEach((img: any) => {
        if (filesPreview.value.length >= props.maxFiles) return;

        const url =
            img.image_url ?? // AGORA usamos a URL vinda da API
            img.url ??       // fallback
            null;

        filesPreview.value.push({
            id: img.id,
            url,
            file: null,
            isOld: true,
        });
    });
});

/* =====================================
   ABRIR INPUT DE ARQUIVO
===================================== */
function triggerFileInput(e: MouseEvent) {
    const target = e.target as HTMLElement;
    if (target.closest(".dropzone-remove-btn")) return;
    fileInput.value?.click();
}

/* =====================================
   UPLOAD DE ARQUIVOS
===================================== */
function handleFiles(event: Event) {
    const input = event.target as HTMLInputElement;
    const selectedFiles = Array.from(input.files || []);

    errorMessage.value = "";

    if (!selectedFiles.length) return;

    // limite de arquivos
    if (filesPreview.value.length >= props.maxFiles) {
        errorMessage.value = `Limite máximo de ${props.maxFiles} imagens atingido.`;
        input.value = "";
        return;
    }

    for (let file of selectedFiles) {
        if (filesPreview.value.length >= props.maxFiles) {
            errorMessage.value = `Limite máximo de ${props.maxFiles} imagens atingido.`;
            break;
        }

        const url = URL.createObjectURL(file);

        filesPreview.value.push({
            url,
            file: markRaw(file), // ← ESSENCIAL
            isOld: false,
        });
    }

    emit("onCoverSelected", filesPreview.value);
    input.value = "";
}

/* =====================================
   REMOVER ARQUIVO
===================================== */
function removeFile(index: number) {
    filesPreview.value.splice(index, 1);
    errorMessage.value = "";
    emit("onCoverSelected", filesPreview.value);
}
</script>

<template>
    <div
        class="relative flex flex-col items-center justify-center w-full min-h-64
               border-2 border-dashed border-gray-300 rounded-xl cursor-pointer
               hover:bg-gray-100 dark:border-white/20 dark:hover:border-white/30
               dark:hover:bg-white/10 transition p-4"
        @click="triggerFileInput"
    >
        <!-- SE EXISTEM ARQUIVOS -->
        <div v-if="filesPreview.length > 0" class="grid grid-cols-3 gap-3 w-full">
            <div
                v-for="(item, index) in filesPreview"
                :key="index"
                class="relative bg-white/80 dark:bg-white/10 p-2 rounded-lg shadow
                       flex flex-col items-center justify-center"
            >
                <!-- Botão remover -->
                <button
                    class="dropzone-remove-btn absolute top-1 right-1 bg-black/60 text-white p-1 rounded-full"
                    @click.stop.prevent="removeFile(index)"
                >
                    <component :is="getIcon('X')" class="w-4 h-4" />
                </button>

                <!-- Thumb -->
                <img
                    v-if="item.url"
                    :src="item.url"
                    class="w-24 h-24 object-cover rounded-md"
                />

                <div v-else class="flex flex-col items-center text-gray-500">
                    <component :is="getIcon('FileIcon')" class="w-10 h-10 mb-1" />
                </div>
            </div>

            <!-- Botão adicionar -->
            <div
                v-if="filesPreview.length < maxFiles"
                class="flex flex-col items-center justify-center h-32 border-2 border-dashed rounded-lg text-gray-400"
            >
                <component :is="getIcon('UploadCloud')" class="w-6 h-6 mb-1" />
                <p class="text-xs">Adicionar</p>
            </div>
        </div>

        <!-- SEM ARQUIVOS -->
        <div v-else class="flex flex-col items-center justify-center text-center py-6">
            <component :is="getIcon('UploadCloud')" class="w-10 h-10 mb-4 text-gray-500 dark:text-gray-400" />

            <p class="mb-2 text-sm text-gray-500 dark:text-gray-400" v-html="titleFileTypes"></p>
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ displayFileTypes }}</p>
        </div>

        <!-- Erros -->
        <p v-if="errorMessage" class="text-red-600 text-sm mt-3 font-medium text-center">
            {{ errorMessage }}
        </p>

        <input
            ref="fileInput"
            type="file"
            class="hidden"
            :multiple="multiple"
            :accept="acceptString"
            @change="handleFiles"
        />
    </div>
</template>
