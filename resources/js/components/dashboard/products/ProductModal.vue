<template>
  <transition name="fade">
    <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
      <div class="w-11/12 max-w-xl rounded-xl border border-border bg-card p-5 text-card-foreground shadow-xl">

        <!-- HEADER -->
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-foreground">
            {{ modalTitle }}
          </h3>
          <button type="button" @click="close" class="text-muted-foreground hover:text-foreground">✕</button>
        </div>

        <!-- BODY -->
        <div class="space-y-3">

          <label class="text-sm font-medium text-foreground">Imagens</label>
          <DropzoneFile
            :initial-files="localProduct.images"
            @onCoverSelected="handleFiles"
            :multiple="true"
            :maxFiles="3"
            :allowed-extensions="['jpg','png','webp']"
            title-file-types="Arraste ou clique para adicionar imagens"
            display-file-types="JPG, PNG, WEBP (máx. 1MB)"
          />

          <label class="text-sm font-medium text-foreground">Nome</label>
          <input v-model="localProduct.name" class="w-full rounded-lg border border-input bg-background px-3 py-2 text-foreground" />

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="text-sm font-medium">Preço</label>
              <input v-model="localProduct.price" class="w-full rounded-lg border border-input bg-background px-3 py-2 text-foreground" />
            </div>
            <div>
              <label class="text-sm font-medium">Preço (desconto)</label>
              <input v-model="localProduct.discount_price" class="w-full rounded-lg border border-input bg-background px-3 py-2 text-foreground" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="text-sm font-medium">Categoria</label>
              <select v-model="localProduct.category_id" class="w-full rounded-lg border border-input bg-background px-3 py-2 text-foreground">
                <option value="" disabled>Selecione</option>
                <option v-for="c in categorias" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>

            <div>
              <label class="text-sm font-medium">Estoque</label>
              <input v-model="localProduct.stock" type="number" class="w-full rounded-lg border border-input bg-background px-3 py-2 text-foreground" />
            </div>
          </div>

          <label class="text-sm font-medium">Descrição</label>
          <textarea
            v-model="localProduct.description"
            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-foreground"
            rows="4"
          ></textarea>

          <div class="grid gap-3 rounded-lg border border-border bg-muted/40 p-3 md:grid-cols-2">
            <div>
              <label class="text-sm font-medium">Ação do botão</label>
              <select v-model="localProduct.conversion_type" class="w-full rounded-lg border border-input bg-background px-3 py-2 text-foreground">
                <option value="cart">Adicionar ao carrinho</option>
                <option value="external">Abrir link externo/afiliado</option>
                <option value="whatsapp">Conversar pelo WhatsApp</option>
                <option value="lead">Abrir formulário de contato</option>
              </select>
            </div>
            <div>
              <label class="text-sm font-medium">Texto do botão</label>
              <input v-model="localProduct.cta_label" maxlength="80" placeholder="Ex.: Ver oferta"
                class="w-full rounded-lg border border-input bg-background px-3 py-2 text-foreground" />
            </div>
            <div v-if="localProduct.conversion_type === 'external'" class="md:col-span-2">
              <label class="text-sm font-medium">URL externa ou link de afiliado</label>
              <input v-model="localProduct.external_url" type="url" placeholder="https://..."
                class="w-full rounded-lg border border-input bg-background px-3 py-2 text-foreground" />
            </div>
          </div>

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
            class="cursor-pointer rounded-lg border border-border px-3 py-1 hover:bg-accent hover:text-accent-foreground"
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

type Category = { id: number; name: string }
type SelectedImage = { id?: number | null; file?: File | null; url?: string | null; preview?: string | null }
type EditableProduct = Record<string, any> & { images?: SelectedImage[] }

const props = defineProps<{
  modelValue: boolean
  novoProduto: EditableProduct
  categorias: Category[]
}>()

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
    conversion_type: props.novoProduto.conversion_type ?? 'cart',
    external_url: props.novoProduto.external_url ?? '',
    cta_label: props.novoProduto.cta_label ?? '',
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

  const payload = {
    ...localProduct.value,
    price: normalizePrice(localProduct.value.price),
    discount_price: normalizePrice(localProduct.value.discount_price),
  }

  emit("save", payload)
  emit("update:modelValue", false)
  saving.value = false
}

/* FECHAR */
function close() {
  emit("update:modelValue", false)
}

/* IMAGENS */
function handleFiles(files: SelectedImage[]) {
  localProduct.value.images = files.map((f) => ({
    id: f.id ?? null,
    file: f.file instanceof File ? f.file : null,
    url: f.url ?? f.preview ?? null,
    isOld: !(f.file instanceof File),
  }))
}

function normalizePrice(value: any): number {
  if (value === null || value === undefined || value === "") {
    return 0
  }

  let v = String(value)
    .replace(/\s/g, "")        // remove espaços
    .replace(/[R$\$]/g, "")    // remove R e $
    .replace(/\./g, "")        // remove separador de milhar
    .replace(",", ".")         // vírgula -> ponto

  const n = parseFloat(v)
  return isNaN(n) ? 0 : n
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
