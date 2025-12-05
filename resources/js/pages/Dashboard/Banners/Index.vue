<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, usePage, router } from '@inertiajs/vue3'
import DropzoneFile from '@/components/ui/dropzone-file/DropzoneFile.vue'
import { ref } from 'vue'
import { route } from 'ziggy-js'
import { getIcon } from '@/lib/iconMap'

const page = usePage()
const banners = ref(page.props.banners || [])

const uploading = ref(false)
const newFiles = ref([])
const titulo = ref('')
const subtitulo = ref('')

/**
 * Quando Dropzone retornar novos arquivos
 */
async function handleFiles(files: any[]) {
  newFiles.value = files
}

async function salvarBanner() {
  if (!newFiles.value.length) return alert("Selecione uma imagem!")

  uploading.value = true

  const first = newFiles.value[0]

  const payload = {
    title: titulo.value,
    subtitle: subtitulo.value,
    image_base64: await toBase64(first.file),
  }

  router.post(route('painel.banners.store'), payload, {
    preserveScroll: true,
    onSuccess: () => {
      titulo.value = ""
      subtitulo.value = ""
      newFiles.value = []
      banners.value = page.props.banners as any[]
    },
    onFinish: () => (uploading.value = false)
  })
}


function removerBanner(id: number) {
  router.delete(route('painel.banners.destroy', id), {
    preserveScroll: true,
    onSuccess: () => {
      banners.value = page.props.banners as any[]
    }
  })
}

function toBase64(file: File) {
  return new Promise((resolve) => {
    const reader = new FileReader()
    reader.onload = () => resolve(reader.result)
    reader.readAsDataURL(file)
  })
}
</script>

<template>

  <Head title="Meus Banners" />

  <AppLayout>
    <div class="p-6 mx-auto container space-y-8">

      <!-- TÍTULO -->
      <header>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Gerenciar Banners</h1>
        <p class="text-gray-500 dark:text-gray-400">
          Adicione banners para sua vitrine. Tamanho recomendado: <b>750x200</b>, até <b>1MB</b>.
        </p>
      </header>

      <!-- DROPZONE -->
      <div class="bg-white dark:bg-slate-900 rounded-xl shadow p-6 border dark:border-white/10">
        <h2 class="text-lg font-semibold mb-3 dark:text-white">Adicionar Banner</h2>

        <!-- 
        <label class="text-sm font-medium text-slate-700 dark:text-slate-200">Título</label>
        <input v-model="titulo" type="text" placeholder="Ex: Super Promoção!"
          class="w-full border rounded-lg px-3 py-2 mb-4 dark:bg-slate-800 dark:border-white/20" />

        
        <label class="text-sm font-medium text-slate-700 dark:text-slate-200">Subtítulo</label>
        <input v-model="subtitulo" type="text" placeholder="Ex: Descontos de até 50% OFF"
          class="w-full border rounded-lg px-3 py-2 mb-4 dark:bg-slate-800 dark:border-white/20" />
      -->
        <DropzoneFile :initial-files="[]" :multiple="false" :maxFiles="1"
          :allowed-extensions="['jpg', 'jpeg', 'png', 'webp']" title-file-types="Clique ou arraste uma imagem"
          display-file-types="JPG, PNG, WEBP – Máx. 1MB" @onCoverSelected="handleFiles" />

        <button :disabled="uploading" @click="salvarBanner"
          class="mt-4 px-5 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition disabled:opacity-50">
          {{ uploading ? 'Enviando...' : 'Salvar Banner' }}
        </button>
      </div>

      <!-- LISTA DE BANNERS -->
      <div class="pt-2">
        <h2 class="text-lg font-semibold mb-3 dark:text-white">Seus Banners</h2>

        <div v-if="banners.length" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
          <div v-for="banner in banners" :key="banner.id"
            class="relative rounded-xl overflow-hidden shadow border bg-white dark:bg-slate-800 dark:border-white/10 group">
            <img :src="banner.image_base64" class="w-full h-40 object-cover" />

            <button @click="removerBanner(banner.id)"
              class="absolute top-2 right-2 p-2 rounded-full bg-black/50 text-white opacity-0 group-hover:opacity-100 transition">
              <component :is="getIcon('Trash')" class="w-4 h-4" />
            </button>
          </div>
        </div>

        <p v-else class="text-gray-400 dark:text-gray-500 text-sm">Nenhum banner enviado ainda.</p>
      </div>

    </div>
  </AppLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity .2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
