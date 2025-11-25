<template>
  <transition name="fade">
    <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
      <div class="w-11/12 max-w-xl rounded-xl bg-white dark:bg-slate-900 p-5 shadow-lg">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-semibold text-lg text-slate-800 dark:text-slate-100">{{ modalTitle }}</h3>
          <button @click="close" class="text-slate-500 hover:text-slate-700">✕</button>
        </div>

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
              <label class="text-sm font-medium text-slate-700 dark:text-slate-200">Preço</label>
              <input v-model="localProduct.price" class="w-full border rounded-lg px-3 py-2" />
            </div>
            <div>
              <label class="text-sm font-medium text-slate-700 dark:text-slate-200">Preço (desconto)</label>
              <input v-model="localProduct.discount_price" class="w-full border rounded-lg px-3 py-2" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="text-sm font-medium text-slate-700 dark:text-slate-200">Categoria</label>
              <select v-model="localProduct.category_id" class="w-full border rounded-lg px-3 py-2">
                <option value="" disabled>Selecione</option>
                <option v-for="c in categorias" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>

            <div>
              <label class="text-sm font-medium text-slate-700 dark:text-slate-200">Estoque</label>
              <input v-model="localProduct.stock" type="number" class="w-full border rounded-lg px-3 py-2" />
            </div>
          </div>

          <label class="text-sm font-medium text-slate-700 dark:text-slate-200">Descrição</label>
          <textarea v-model="localProduct.description" class="w-full border rounded-lg px-3 py-2" rows="4"></textarea>

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

        <div class="mt-4 flex justify-end gap-3">
          <button class="px-3 py-1 rounded-lg border cursor-pointer" type="button" @click="close">Cancelar</button>
          <button class="px-4 py-2 rounded-lg bg-emerald-500 text-white cursor-pointer" type="button" @click="save" :disabled="saving">
            {{ saving ? 'Salvando...' : 'Salvar' }}
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup lang="ts">
import { ref, watch, reactive } from 'vue'
import DropzoneFile from '@/components/ui/dropzone-file/DropzoneFile.vue'

const props = defineProps({
  modelValue: { type: Boolean, required: true },
  novoProduto: { type: Object, required: true },
  categorias: { type: Array, required: true },
  savingProp: { type: Boolean, default: false },
})

const emit = defineEmits<{ (e:'update:modelValue', v:boolean):void; (e:'save', p:any):void }>()

const localProduct = reactive(JSON.parse(JSON.stringify(props.novoProduto || {
  id: null, name:'', price:'', discount_price:'', category_id:'', stock:0, description:'', is_public:true, featured:false, images:[]
})))

const modalTitle = `Editando ${localProduct.name}`
const saving = ref(false)

watch(() => props.novoProduto, (v) => {  
  Object.assign(localProduct, JSON.parse(JSON.stringify(v || {})))
  localProduct.featured = (localProduct.featured === 1 ? true : false)
})

watch(() => props.modelValue, (v) => {
  if (!v) {
    // reset localProduct to avoid stale state
    Object.assign(localProduct, JSON.parse(JSON.stringify(props.novoProduto || {})))
  }
})

function close() {
  emit('update:modelValue', false)
}

async function save() {
  saving.value = true
  try {
    // Emit save with the localProduct payload; parent handles persistence
    
    emit('save', JSON.parse(JSON.stringify(localProduct)))
    emit('update:modelValue', false)
  } finally {
    saving.value = false
  }
}

async function handleFiles(files: any) {
  const arr = Array.isArray(files) ? files : []
  
  const out = await Promise.all(
    arr.map(async (f:any) => {
      if (f.file) {
        const base64 = await fileToBase64(f.file)
        return {
          id: null,
          image_path: null,
          image_base64: base64,
          is_cover: false
        }
      }
      if (f.url) {
        return {
          id: null,
          image_path: null,
          image_base64: f.url,
          is_cover: false
        }
      }
      return null
    })
  )

  const cleaned = out.filter(Boolean)

  console.log("IMAGENS:", cleaned.length)
  localProduct.images = cleaned
}

function fileToBase64(file: File): Promise<string> {
  return new Promise((resolve, reject) => {
    const reader = new FileReader()
    reader.onload = e => resolve(e.target?.result as string)
    reader.onerror = reject
    reader.readAsDataURL(file)
  })
}

</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity .15s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
