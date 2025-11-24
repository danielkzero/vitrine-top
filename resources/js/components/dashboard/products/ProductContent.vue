<template>
    <div v-if="page.type === 'products'" class="space-y-4">
        <p class="text-slate-600 text-sm leading-relaxed">
            Os <b>produtos</b> do seu catálogo. Gerencie categorias e produtos — adicione, edite ou remova.
        </p>

        <!-- Header: ações -->
        <div class="flex items-center gap-3">
            <div class="overflow-x-auto p-2 bg-gray-100 w-full rounded-lg">
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

            <div v-if="!produtosFiltrados.length" class="text-gray-400 text-sm italic mt-3">
                Nenhum produto nesta categoria.
            </div>
        </div>

        <!-- Modal: Nova Categoria -->
        <div v-if="showAddCategoryLocal" class="fixed inset-0 z-40 flex items-center justify-center bg-black/50">
            <div class="bg-white dark:bg-slate-900 p-4 rounded-xl w-11/12 max-w-sm">
                <h4 class="font-semibold mb-2">Nova Categoria</h4>
                <input v-model="novaCategoriaLocal" class="w-full border rounded-lg px-3 py-2 mb-3"
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
    // prepare novoProdutoLocal
    novoProdutoLocal.value = {
        id: null, name: '', price: '', discount_price: '', category_id: categoriaSelecionadaLocal.value ?? '', stock: 0, description: '', is_public: true, featured: false, images: []
    }
    showAddProductLocal.value = true
}

function onEditProduct(prod: any) {
    // clone product into novoProdutoLocal and open modal
    novoProdutoLocal.value = JSON.parse(JSON.stringify(prod))
    showAddProductLocal.value = true
}

// When modal emits save, pass to parent salvarProduto (which expects novoProduto)
function handleSaveProduct(payload: any) {
    // parent salvarProduto expects novoProduto data available on parent scope.
    // We call parent salvarProduto with payload (adapt parent to accept param or send via router)
    // To keep compatibility with your existing salvarProduto, emit product through router here:    
    props.salvarProduto && props.salvarProduto(payload)
    // Alternatively, if parent expects to get novoProduto var mutated, you could assign.
}

// Save category from modal
function saveCategoria() {
    console.log(novaCategoriaLocal.value)
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
