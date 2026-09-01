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
const newFiles = ref<File[]>([])
const titulo = ref('')
const subtitulo = ref('')

/**
 * Dropzone retorna os arquivos selecionados.
 * Removemos qualquer base64 e guardamos apenas o FILE real.
 */
async function handleFiles(files: any[]) {
  newFiles.value = files
}

/**
 * Enviar banner com FILE real (sem base64)
 */
async function salvarBanner() {
  if (!newFiles.value.length) return alert("Selecione uma imagem!")

  uploading.value = true

  const form = new FormData()
  const first = newFiles.value[0]

  form.append("title", titulo.value)
  form.append("subtitle", subtitulo.value)

  // se o DropzoneFile retorna {file: File, url: string}
  form.append("image", first.file ?? first)  

  router.post(route("painel.banners.store"), form, {
    forceFormData: true,
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
  router.delete(route("painel.banners.destroy", id), {
    preserveScroll: true,
    onSuccess: () => {
      banners.value = page.props.banners as any[]
    }
  })
}
</script>


<template>

  <Head title="Meus Banners" />

  <AppLayout>
    <div class="p-6 mx-auto container space-y-8">

      <!-- TÍTULO -->
      <header>
        <h1 class="text-3xl font-bold text-foreground">Gerenciar Banners</h1>
        <p class="text-muted-foreground">
          Adicione banners para sua vitrine. Tamanho recomendado: <b>750x200</b>, até <b>1MB</b>.
        </p>
      </header>

      <!-- DROPZONE -->
      <div class="rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm">
        <h2 class="mb-3 text-lg font-semibold">Adicionar Banner</h2>

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
        <h2 class="mb-3 text-lg font-semibold text-foreground">Seus Banners</h2>

        <div v-if="banners.length" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
          <div v-for="banner in banners" :key="banner.id"
            class="group relative overflow-hidden rounded-xl border border-border bg-card shadow-sm">
            <img :src="banner.image_url" class="w-full h-40 object-cover" />
            <button @click="removerBanner(banner.id)"
              class="absolute top-2 right-2 p-2 rounded-full bg-black/50 text-white opacity-0 group-hover:opacity-100 transition">
              <component :is="getIcon('Trash')" class="w-4 h-4" />
            </button>
          </div>
        </div>

        <p v-else class="text-sm text-muted-foreground">Nenhum banner enviado ainda.</p>
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
