//DropzoneFile.vue
<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { UploadCloud, File as FileIcon, X } from "lucide-vue-next";

const emit = defineEmits(["removeOldImage", "onCoverSelected"]);

const props = defineProps({
	displayFileTypes: { type: String, default: "" },
	titleFileTypes: { type: String, default: "" },
	allowedExtensions: { type: Array, default: () => [] },
	multiple: { type: Boolean, default: false },
	maxFiles: { type: Number, default: 1 },

	// Array vindo do backend
	// [{ id, image_path, image_base64 }]
	initialFiles: { type: Array, default: () => [] }
});

// Lista final
const filesPreview = ref<
	{
		id?: number;
		url: string | null;
		file: File | null;
		isOld?: boolean;
	}[]
>([]);

const acceptString = computed(() => {
	return props.allowedExtensions.map(ext => `.${ext}`).join(",");
});

// -------------------------------
// 1. Inserir imagens que já existem no BD
// -------------------------------
onMounted(() => {
	props.initialFiles.forEach((img: any) => {
		if (filesPreview.value.length >= props.maxFiles) return;

		let url = null;

		if (img.image_base64) {
			url = img.image_base64;
		} else if (img.image_path) {
			url = "/" + img.image_path; // ajuste conforme storage:link
		}

		filesPreview.value.push({
			id: img.id,
			url,
			file: null,
			isOld: true
		});
	});
});

// -------------------------------
// 2. Upload normal
// -------------------------------
function handleFiles(event: Event) {
    const input = event.target as HTMLInputElement;
    const selectedFiles = Array.from(input.files || []);

    const processedFiles: File[] = [];

    for (let file of selectedFiles) {
        // Limite máximo
        if (filesPreview.value.length >= props.maxFiles) break;

        let fileObj = file;
        let url = file.type.startsWith("image/") ? URL.createObjectURL(file) : null;

        // Se por algum motivo for base64 string (apenas para antigos)
        if (typeof file === "string" && file.startsWith("data:image/")) {
            const extensao = file.split(';')[0].split('/')[1];
            fileObj = base64ToFile(file, `new_file_${Date.now()}.${extensao}`);
            url = URL.createObjectURL(fileObj);
        }

        filesPreview.value.push({
            url,
            file: fileObj,
            isOld: false
        });

        processedFiles.push(fileObj);
    }

    console.log('filesPreview after adding files:', filesPreview.value);

    emit("onCoverSelected", processedFiles);
}

// Função auxiliar para converter base64 em File
function base64ToFile(base64: string, filename: string): File {
    const arr = base64.split(',');
    const mime = arr[0].match(/:(.*?);/)?.[1] || 'image/jpeg';

    const bstr = atob(arr[1]);
    let n = bstr.length;
    const u8arr = new Uint8Array(n);

    while (n--) u8arr[n] = bstr.charCodeAt(n);

    return new File([u8arr], filename, { type: mime });
}

// -------------------------------
// 3. Remover imagem (nova ou antiga)
// -------------------------------
function removeFile(index: number) {
	filesPreview.value.splice(index, 1);
	// retornar todo os arquivos atuais
	emit("onCoverSelected", filesPreview.value);
}
</script>


<template>
	<label for="dropzone-file" class="relative flex flex-col items-center justify-center w-full min-h-64
               border-2 border-dashed border-gray-300 rounded-xl cursor-pointer
               hover:bg-gray-100 dark:border-white/20 dark:hover:border-white/30
               dark:hover:bg-white/10 transition p-4">
		<!-- Se houver arquivos -->
		<div v-if="filesPreview.length > 0" class="grid grid-cols-3 gap-3 w-full">

			<div v-for="(item, index) in filesPreview" :key="index"
				class="relative bg-white/80 dark:bg-white/10 p-2 rounded-lg shadow flex flex-col items-center justify-center">
				<!-- Botão de remover -->
				<button @click.stop.prevent="removeFile(index)"
					class="absolute top-1 right-1 bg-black/60 text-white p-1 rounded-full">
					<X class="w-4 h-4" />
				</button>

				<!-- Se imagem, mostra thumbnail -->
				<img v-if="item.url" :src="item.url || item.image_base64" class="w-full h-24 object-cover rounded-md" />

				<!-- Se não imagem, ícone -->
				<div v-else class="flex flex-col items-center text-gray-500">
					<FileIcon class="w-10 h-10 mb-1" />
					<span class="text-xs text-center truncate">{{ item.file.name }}</span>
				</div>
			</div>

			<!-- Card para adicionar mais imagens -->
			<div v-if="filesPreview.length < maxFiles"
				class="flex flex-col items-center justify-center h-32 border-2 border-dashed rounded-lg text-gray-400">
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

		<input id="dropzone-file" type="file" class="hidden" :multiple="multiple" :accept="acceptString"
			@change="handleFiles" />
	</label>
</template>
