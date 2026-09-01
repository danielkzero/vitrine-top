<template>
    <div v-if="page.type === 'products'" class="space-y-4">
        <section class="grid gap-3 rounded-xl border border-border bg-muted/40 p-4 md:grid-cols-[220px_1fr]">
            <div>
                <label for="catalog-mode" class="text-sm font-semibold text-foreground">Modo do catálogo</label>
                <p class="mt-1 text-xs text-muted-foreground">Define a experiência padrão desta página.</p>
            </div>
            <div>
                <select id="catalog-mode" v-model="page.catalog_mode"
                    class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground">
                    <option value="store">Loja própria — carrinho e checkout</option>
                    <option value="affiliate">Afiliados — links para ofertas externas</option>
                    <option value="presell">Presell — conteúdo persuasivo antes da oferta</option>
                    <option value="showcase">Vitrine — apresentação e contato</option>
                    <option value="hybrid">Híbrido — produtos próprios e externos</option>
                </select>
                <p class="mt-2 text-xs text-muted-foreground">{{ catalogModeHelp }}</p>
            </div>
        </section>

        <p class="text-sm leading-relaxed text-muted-foreground">
            Os <b>produtos</b> do seu catálogo. Gerencie categorias e produtos — adicione, edite ou remova.
        </p>

        <!-- Header: ações -->
        <div class="flex items-center gap-3">
            <div class="w-full overflow-x-auto rounded-lg bg-muted p-2">
                <div class="flex gap-2">
                    <button @click="showAddCategoryLocal = true" type="button"
                        class="cursor-pointer inline-flex whitespace-nowrap items-center text-sm gap-2 px-3 py-1 rounded-lg bg-emerald-500 text-white">
                        <component :is="getIcon('PlusCircle')" class="w-4 h-4" />
                        Nova Categoria
                    </button>
                    <CategoryPill v-for="c in categoriasReverse" :key="c.id" :category="c"
                        :selected="categoriaSelecionadaLocal === c.id" @select="onSelectCategory"
                        @delete="onDeleteCategory" />
                </div>
            </div>
        </div>

        <!-- produtos list -->
        <div v-if="categoriaSelecionadaLocal" class="mt-2">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold">{{ nomeCategoria(categoriaSelecionadaLocal) }}</h3>
                <button type="button" class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-sky-500 text-white cursor-pointer"
                    @click="openNewProduct">
                    <component :is="getIcon('Plus')" class="w-4 h-4" /> Adicionar
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <ProductCard v-for="produto in produtosFiltrados" :key="produto.id ?? produto._localId"
                    :product="produto" :formatCurrency="formatCurrency" :categorias="categorias"
                    @edit="onEditProduct" />
            </div>

            <div v-if="!produtosFiltrados.length" class="mt-3 text-sm italic text-muted-foreground">
                Nenhum produto nesta categoria.
            </div>
        </div>

        <!-- Modal: Nova Categoria -->
        <div v-if="showAddCategoryLocal" class="fixed inset-0 z-40 flex items-center justify-center bg-black/50">
            <div class="w-11/12 max-w-sm rounded-xl border border-border bg-card p-4 text-card-foreground shadow-xl">
                <h4 class="font-semibold mb-2">Nova Categoria</h4>
                <input v-model="novaCategoriaLocal" class="mb-3 w-full rounded-lg border border-input bg-background px-3 py-2 text-foreground"
                    placeholder="Nome da categoria" />
                <div class="flex justify-end gap-2">
                    <button class="px-3 py-1" @click="showAddCategoryLocal = false">Cancelar</button>
                    <button class="px-3 py-1 rounded-lg bg-emerald-500 text-white"
                        @click="saveCategoria">Salvar</button>
                </div>
            </div>
        </div>

        <!-- Product Modal -->
        <ProductModal v-model="showAddProductLocal" :novoProduto="novoProdutoLocal" :categorias="categorias" 
            @save="handleSaveProduct" />
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import ProductCard from '@/components/dashboard/products/ProductCard.vue'
import CategoryPill from '@/components/dashboard/products/CategoryPill.vue'
import ProductModal from '@/components/dashboard/products/ProductModal.vue'
import { getIcon } from '@/lib/iconMap'
import { formatCurrency } from '@/lib/utils'

// Props expected from parent (Edit.vue)
const props = defineProps({
    categorias: { type: Array, required: true },
    removerCategoria: { type: Function, required: true },
    nomeCategoria: { type: Function, required: true },
    produtos: { type: Array, required: true },
    novaCategoria: { type: String, default: '' },
    salvarCategoria: { type: Function, required: true },
    salvarProduto: { type: Function, required: true },
    editarProduto: { type: Function, required: true },
    onCoverSelected: { type: Function, required: true },
    page: { type: Object, required: true }
})

// local state to avoid mutating parent directly (keeps compatibility)
const categoriaSelecionadaLocal = ref(props.categorias.length ? props.categorias[props.categorias.length - 1].id : null)
const showAddCategoryLocal = ref(false)
const showAddProductLocal = ref(false)
const novoProdutoLocal = ref<any>({
    id: null, name: '', price: '', discount_price: '', category_id: categoriaSelecionadaLocal.value, stock: 0, description: '', is_public: true, featured: false, images: []
})

// keep novaCategoria local copy
const novaCategoriaLocal = ref(props.novaCategoria || '')

watch(() => props.novaCategoria, (v) => novaCategoriaLocal.value = v || '')

// expose helper
const categoriasReverse = computed(() => [...props.categorias].reverse())

const catalogModeHelp = computed(() => ({
    store: 'Todos os produtos usam a compra interna, salvo quando você alterar a ação individual.',
    affiliate: 'Ideal para comissionados: os botões direcionam para páginas externas de parceiros.',
    presell: 'Use o conteúdo da página para apresentar benefícios, provas e contexto antes dos produtos.',
    showcase: 'Apresenta o catálogo sem checkout; prioriza WhatsApp ou formulário de contato.',
    hybrid: 'Misture carrinho, links externos, WhatsApp e captura de contato no mesmo catálogo.',
}[props.page.catalog_mode || 'store']))

const produtosFiltrados = computed(() => {
    return (props.produtos || []).filter((p: any) => p.category_id === categoriaSelecionadaLocal.value)
})

function onSelectCategory(id: number) {
    categoriaSelecionadaLocal.value = id
}

function onDeleteCategory(category: any) {
    // ask parent to remove
    props.removerCategoria(category)
    // adjust selected if needed
    if (categoriaSelecionadaLocal.value === category.id) {
        categoriaSelecionadaLocal.value = props.categorias.length ? props.categorias[0]?.id ?? null : null
    }
}

function openNewProduct() {
    novoProdutoLocal.value = {
        id: null,
        name: '',
        price: '',
        discount_price: '',
        category_id: categoriaSelecionadaLocal.value ?? '',
        stock: 0,
        description: '',
        is_public: true,
        featured: false,
        images: [],
        conversion_type: props.page.catalog_mode === 'affiliate' ? 'external' : props.page.catalog_mode === 'showcase' ? 'whatsapp' : 'cart',
        external_url: '',
        cta_label: '',
    }
    showAddProductLocal.value = true
}

function onEditProduct(prod: any) {
    const clone = JSON.parse(JSON.stringify(prod))

    // Converter imagens do backend para formato esperado pelo Dropzone
    clone.images = (clone.images || []).map((img: any) => ({
        id: img.id ?? null,
        file: null,
        url: img.image_path ? img.image_path : null,
        isOld: true,
    }))

    novoProdutoLocal.value = clone
    showAddProductLocal.value = true
}


function handleSaveProduct(payload: any) {  
    props.salvarProduto && props.salvarProduto(payload)
}

// Save category from modal
function saveCategoria() {
    if (!novaCategoriaLocal.value || !novaCategoriaLocal.value.trim()) return
    // update local and ask parent to persist
    props.salvarCategoria && props.salvarCategoria(novaCategoriaLocal.value)
    novaCategoriaLocal.value = ''
    showAddCategoryLocal.value = false
}


</script>

<style scoped>
/* pequenas helpers visuais */
</style>
