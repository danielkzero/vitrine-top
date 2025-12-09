<!-- resources/js/components/vitrine/public/ProductsPublic.vue -->
<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import ProductCard from '@/components/vitrine/public/ProductCard.vue'
import ProductCardSkeleton from '@/components/vitrine/public/ProductCardSkeleton.vue'
import { getIcon } from '@/lib/iconMap'
import debounce from 'lodash/debounce'
import BannerCarousel from './BannerCarousel.vue'
import { route } from 'ziggy-js'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    page: Object,
    products: Array,
    categories: Array,
    user: Object,
    settings: Object,
    banners: Object
})


// mobile-first state
const q = ref('')
const selectedCategory = ref(props.page?.category_id ?? null)
const viewMode = ref<'grid' | 'list'>('grid')
const showModal = ref(false)
const activeProduct = ref(null)
const perPage = ref(12)
const loading = ref(true)

// Produtos públicoss (filtra is_public e category)
const publicProducts = computed(() => {
    let items = (props.products ?? []).filter(p => !!p.is_public)

    if (selectedCategory.value) {
        items = items.filter(p => p.category_id === selectedCategory.value)
    }

    if (q.value && q.value.trim().length) {
        const term = q.value.toLowerCase()
        items = items.filter(p => (p.name || '').toLowerCase().includes(term) || (p.description || '').toLowerCase().includes(term))
    }

    // featured first, then by id desc
    items.sort((a, b) => Number(b.featured) - Number(a.featured) || (b.id - a.id))
    return items
})

// paginação simples "infinite scroll" local (carrega mais itens ao chegar no fim)
const visibleCount = ref(perPage.value)
const visibleProducts = computed(() => publicProducts.value.slice(0, visibleCount.value))

// carregar mais
function loadMore() {
    if (visibleCount.value >= publicProducts.value.length) return
    loading.value = true
    // simular pequena latência para animação skeleton
    setTimeout(() => {
        visibleCount.value = Math.min(publicProducts.value.length, visibleCount.value + perPage.value)
        loading.value = false
    }, 400)
}

// debounce busca
const onSearch = debounce((v) => {
    visibleCount.value = perPage.value
}, 300)

// watcher busca
watch(q, (v) => onSearch(v))

// abre modal
function openProduct(prod) {
    router.get(
        route('vitrine.public.page.id', {
            slug: props.user.slug,
            page: props.page.key,
            id: prod.id
        })/*,
        { preserveScroll: true }*/
    )
    /*
    activeProduct.value = prod
    showModal.value = true*/
    loading.value = false
}

// fechar
function closeModal() {
    showModal.value = false
    activeProduct.value = null
}

// featured carousel (apenas produtos com featured)
const featured = computed(() => (props.products ?? []).filter(p => p.is_public && p.featured))

// infinite scroll usando sentinel element
const sentinel = ref<HTMLElement | null>(null)
onMounted(() => {
    const io = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) loadMore()
        })
    }, { root: null, rootMargin: '200px', threshold: 0.1 })

    if (sentinel.value) io.observe(sentinel.value)
})

watch(
    () => props.products,
    (v) => {
        if (Array.isArray(v) && v.length) {
            loading.value = false
        }
    },
    { immediate: true }
)


const STORAGE_KEY = 'vitrine_view_mode'

onMounted(() => {
    const saved = localStorage.getItem(STORAGE_KEY)
    if (saved === 'grid' || saved === 'list') {
        viewMode.value = saved
    }
})

function toggleViewMode() {
    viewMode.value = viewMode.value === 'grid' ? 'list' : 'grid'
    localStorage.setItem(STORAGE_KEY, viewMode.value)
}

</script>

<template>
    <div class="min-h-screen bg-slate-50 text-slate-900">
        <!-- Header: busca + ações -->
        <header class="pb-4">
            <div class="flex items-center gap-3">
                <!-- Acessar carrinho de compras -->
                <button class="p-2 bg-white rounded-xl shadow-sm border border-slate-100">
                    <component :is="getIcon('ShoppingCart')" fill="currentColor" class="w-5 h-5 text-slate-700" />
                </button>

                <div class="flex-1 relative">
                    <button class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                        <component :is="getIcon('Search')" class="w-4 h-4" />
                    </button>
                    <input v-model="q" type="search" placeholder="Pesquisar produtos..."
                        class="w-full pl-10 pr-3 py-2 rounded-xl bg-white border border-slate-100 focus:ring-0 text-sm" />
                    <button v-if="q" @click="q = ''" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
                        <component :is="getIcon('X')" class="w-4 h-4" />
                    </button>
                </div>

                <button type="button" @click="toggleViewMode"
                    class="p-2 bg-white rounded-xl shadow-sm border border-slate-100 ml-2">
                    <component :is="getIcon(viewMode === 'grid' ? 'LayoutGrid' : 'List')"
                        class="w-5 h-5 text-slate-700" />
                </button>
            </div>
            <!-- ============================ -->
            <!-- BANNER CAROUSEL (opcional) -->
            <!-- ============================ -->
            <BannerCarousel :images="props.banners" v-if="props.banners.length" />

            <!-- categorias scroll -->
            <h2 class="text-xl font-bold items-center flex my-6">
                Categorias
            </h2>
            <div class="mt-3 overflow-x-auto pb-2">
                <div class="flex gap-2">
                    <button
                        :class="['px-3 py-1.5 rounded-xl text-sm whitespace-nowrap', !selectedCategory ? `text-white` : 'bg-white border']"
                        :style="!selectedCategory ? { backgroundColor: props.user.theme_color } : ''"
                        @click="selectedCategory = null">Todas</button>

                    <button v-for="c in categories" :key="c.id"
                        :class="['px-3 py-1.5 rounded-xl text-sm whitespace-nowrap', selectedCategory === c.id ? `text-white` : 'bg-white border']"
                        :style="selectedCategory === c.id ? { backgroundColor: props.user.theme_color } : ''"
                        @click="selectedCategory = c.id">
                        <component :is="getIcon(c.icon || 'Store')" class="inline-block w-4 h-4 mr-2" />
                        {{ c.name }}
                    </button>
                </div>
            </div>
        </header>

        <!-- Grid / List -->
        <main class=" pb-2">
            <div v-if="!visibleProducts.length && !loading" class="text-center py-12 text-slate-400">
                Nenhum produto disponível.
            </div>
            <div v-if="viewMode === 'grid'" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-3 gap-3">
                <ProductCard v-for="p in visibleProducts" :key="p.id" :product="p" viewMode="grid" @open="openProduct"
                    :user="props.user" />

                <!-- skeletons -->
                <ProductCardSkeleton v-if="loading" v-for="n in 6" :key="'sk' + n" />
            </div>

            <div v-else class="space-y-3">
                <ProductCard v-for="p in visibleProducts" :key="p.id" :product="p" viewMode="list" @open="openProduct"
                    :user="props.user" />

                <ProductCardSkeleton v-if="loading" v-for="n in 3" :key="'skl' + n" />
            </div>

            <!-- sentinel pro infinite scroll -->
            <div ref="sentinel" class="h-6"></div>
        </main>


        <!--<FloatingWhatsApp :user="props.user" />-->
    </div>
</template>

<style scoped>
/* regras mobile-first leves */
</style>
