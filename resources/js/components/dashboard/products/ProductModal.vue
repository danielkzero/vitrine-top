<template>
  <transition name="fade">
    <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
      <div class="w-11/12 max-w-xl rounded-xl bg-white dark:bg-slate-900 p-5 shadow-lg">

        <!-- HEADER -->
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-semibold text-lg text-slate-800 dark:text-slate-100">
            {{ modalTitle }}
          </h3>
          <button type="button" @click="close" class="text-slate-500 hover:text-slate-700">✕</button>
        </div>

        <!-- BODY -->
        <div class="space-y-3">

          <label class="text-sm font-medium text-slate-700 dark:text-slate-200">Imagens</label>
          <DropzoneFile
            :initial-files="localProduct.images"
            @onCoverSelected="handleFiles"
            :multiple="true"
            :maxFiles="3"
            :allowed-extensions="['jpg','png','webp']"
            title-file-types="Arraste ou clique para adicionar imagens"
            display-file-types="JPG, PNG, WEBP (máx. 1MB)"
          />

          <label class="text-sm font-medium text-slate-700 dark:text-slate-200">Nome</label>
          <input v-model="localProduct.name" class="w-full border rounded-lg px-3 py-2" />

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="text-sm font-medium">Preço</label>
              <input v-model="localProduct.price" class="w-full border rounded-lg px-3 py-2" />
            </div>
            <div>
              <label class="text-sm font-medium">Preço (desconto)</label>
              <input v-model="localProduct.discount_price" class="w-full border rounded-lg px-3 py-2" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="text-sm font-medium">Categoria</label>
              <select v-model="localProduct.category_id" class="w-full border rounded-lg px-3 py-2">
                <option value="" disabled>Selecione</option>
                <option v-for="c in categorias" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>

            <div>
              <label class="text-sm font-medium">Estoque</label>
              <input v-model="localProduct.stock" type="number" class="w-full border rounded-lg px-3 py-2" />
            </div>
          </div>

          <label class="text-sm font-medium">Descrição</label>
          <textarea
            v-model="localProduct.description"
            class="w-full border rounded-lg px-3 py-2"
            rows="4"
          ></textarea>

          <div class="flex items-center gap-4">
            <label class="inline-flex items-center gap-2">
              <input type="checkbox" v-model="localProduct.is_public" />
              <span class="text-sm">Produto público</span>
            </label>

            <label class="inline-flex items-center gap-2">
              <input type="checkbox" v-model="localProduct.featured" />
              <span class="text-sm">Destaque</span>
            </label>
          </div>
        </div>

        <!-- FOOTER -->
        <div class="mt-4 flex justify-end gap-3">
          <button
            class="px-3 py-1 rounded-lg border cursor-pointer"
            type="button"
            @click="close"
          >
            Cancelar
          </button>

          <button
            class="px-4 py-2 rounded-lg bg-emerald-500 text-white cursor-pointer"
            type="button"
            @click="save"
            :disabled="saving"
          >
            {{ saving ? 'Salvando...' : 'Salvar' }}
          </button>
        </div>

      </div>
    </div>
  </transition>
</template>

<script setup lang="ts">
import { ref, watch } from "vue"
import DropzoneFile from "@/components/ui/dropzone-file/DropzoneFile.vue"

const props = defineProps({
  modelValue: { type: Boolean, required: true },
  novoProduto: { type: Object, required: true },
  categorias: { type: Array, required: true },
})

const emit = defineEmits(["update:modelValue", "save"])

/* RESET SIMPLES DO PRODUTO */
function resetProduct() {
  return {
    id: props.novoProduto.id ?? null,
    name: props.novoProduto.name ?? "",
    price: props.novoProduto.price ?? "",
    discount_price: props.novoProduto.discount_price ?? "",
    category_id: props.novoProduto.category_id ?? "",
    stock: props.novoProduto.stock ?? 0,
    description: props.novoProduto.description ?? "",
    is_public: props.novoProduto.is_public ?? true,
    featured: props.novoProduto.featured ?? false,
    images: Array.isArray(props.novoProduto.images)
      ? props.novoProduto.images.map(img => ({ ...img }))
      : [],
  }
}

const localProduct = ref(resetProduct())
const saving = ref(false)

const modalTitle = `Editando ${localProduct.value.name}`

/* QUANDO O MODAL ABRE, RESETA */
watch(
  () => props.modelValue,
  (abriu) => {
    if (abriu) {
      localProduct.value = resetProduct()
    }
  }
)

/* SALVAR */
async function save() {
  saving.value = true
  emit("save", localProduct.value)
  emit("update:modelValue", false)
  saving.value = false
}

/* FECHAR */
function close() {
  emit("update:modelValue", false)
}

/* IMAGENS */
function handleFiles(files) {
  localProduct.value.images = files.map(f => ({
    id: f.id ?? null,
    file: f.file instanceof File ? f.file : null,
    url: f.url ?? f.preview ?? null,
    isOld: !(f.file instanceof File),
  }))
}
</script>


<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
