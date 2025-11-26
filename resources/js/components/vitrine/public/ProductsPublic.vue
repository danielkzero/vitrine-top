<!-- resources/js/components/vitrine/public/ProductsPublic.vue -->
<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import ProductCard from '@/components/vitrine/public/ProductCard.vue'
import ProductModalFull from '@/components/vitrine/public/ProductModalFull.vue'

import FloatingWhatsApp from '@/components/vitrine/public/FloatingWhatsApp.vue'
import ProductCardSkeleton from '@/components/vitrine/public/ProductCardSkeleton.vue'
import { getIcon } from '@/lib/iconMap'
import debounce from 'lodash/debounce'
import BannerCarousel from './BannerCarousel.vue'

const props = defineProps({
    page: Object,
    products: Array,
    categories: Array,
    user: Object,
    settings: Object
})

// mobile-first state
const q = ref('')
const selectedCategory = ref(props.page?.category_id ?? null)
const viewMode = ref<'grid' | 'list'>('grid')
const showModal = ref(false)
const activeProduct = ref(null)
const perPage = ref(12)
const loading = ref(false)

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
    activeProduct.value = prod
    showModal.value = true
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

const banner = ref({
  settings: {
    show_header: true,
    banners: [
      "https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=1600&auto=format&fit=crop&q=80",
      "https://images.unsplash.com/photo-1503602642458-232111445657?w=1600&auto=format&fit=crop&q=80",
      "https://images.unsplash.com/photo-1491553895911-0055eca6402d?w=1600&auto=format&fit=crop&q=80",
      "https://images.unsplash.com/photo-1483985988355-763728e1935b?w=1600&auto=format&fit=crop&q=80",
      "https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=1600&auto=format&fit=crop&q=80"
    ]
  }
});

</script>

<template>
    <div class="min-h-screen bg-slate-50 text-slate-900">
        <!-- Header: busca + ações -->
        <header class="pb-4">
            <div class="flex items-center gap-3">
                <button class="p-2 bg-white rounded-xl shadow-sm border border-slate-100">
                    <component :is="getIcon('Menu')" class="w-5 h-5 text-slate-700" />
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

                <button @click="viewMode = viewMode === 'grid' ? 'list' : 'grid'"
                    class="p-2 bg-white rounded-xl shadow-sm border border-slate-100 ml-2">
                    <component :is="getIcon(viewMode === 'grid' ? 'LayoutGrid' : 'List')"
                        class="w-5 h-5 text-slate-700" />
                </button>
            </div>

            <!-- ============================ -->
            <!-- BANNER CAROUSEL (opcional) -->
            <!-- ============================ -->
            <BannerCarousel :images="banner.settings.banners" v-if="banner.settings.banners.length" />

            <!-- categorias scroll -->
            <h2 class="text-xl font-bold items-center flex my-3">
                Categorias
            </h2>
            <div class="mt-3 overflow-x-auto pb-2">
                <div class="flex gap-2">
                    <button
                        :class="['px-3 py-1.5 rounded-xl text-sm whitespace-nowrap', !selectedCategory ? 'bg-emerald-500 text-white' : 'bg-white border']"
                        @click="selectedCategory = null">Todas</button>

                    <button v-for="c in categories" :key="c.id"
                        :class="['px-3 py-1.5 rounded-xl text-sm whitespace-nowrap', selectedCategory === c.id ? 'bg-emerald-500 text-white' : 'bg-white border']"
                        @click="selectedCategory = c.id">
                        <component :is="getIcon(c.icon || 'Store')" class="inline-block w-4 h-4 mr-2" />
                        {{ c.name }}
                    </button>
                </div>
            </div>
        </header>

        <!-- Destaques carousel (mobile-first small cards)
        <section v-if="featured.length" class=" pb-4">
            <div class="flex gap-3 overflow-x-auto pb-2">
                <div v-for="f in featured" :key="f.id"
                    class="min-w-[220px] bg-gradient-to-tr from-sky-400 to-emerald-500 text-white rounded-xl p-4 shadow-lg cursor-pointer"
                    @click="openProduct(f)">
                    <div class="flex items-center gap-3">
                        <div class="flex-1">
                            <h4 class="font-bold text-lg line-clamp-2">{{ f.name }}</h4>
                            <p class="text-sm opacity-90 mt-1">R$ {{ Number(f.discount_price || f.price).toFixed(2) }}
                            </p>
                        </div>
                        <img v-if="f.images?.length" :src="f.images[0].image_base64 || ('/' + f.images[0].image_path)"
                            class="w-20 h-20 object-cover rounded-lg" loading="lazy" />
                    </div>
                </div>
            </div>
        </section> -->

        <!-- Grid / List -->
        <main class=" pb-2">
            <div v-if="!visibleProducts.length && !loading" class="text-center py-12 text-slate-400">
                Nenhum produto disponível.
            </div>

            <div v-if="viewMode === 'grid'" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-5 gap-3">
                <ProductCard v-for="p in visibleProducts" :key="p?.id" :product="p" @open="openProduct" />
                <!-- skeletons -->
                <ProductCardSkeleton v-if="loading" v-for="n in 6" :key="'sk' + n" />
            </div>

            <div v-else class="space-y-3">
                <div v-for="p in visibleProducts" :key="p.id"
                    class="bg-white rounded-xl p-3 shadow-sm flex gap-3 items-center cursor-pointer"
                    @click="openProduct(p)">
                    <img v-if="p.images?.length" :src="p.images[0].image_base64 || ('/' + p.images[0].image_path)"
                        class="w-20 h-20 object-cover rounded-lg" loading="lazy" />
                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <h4 class="font-bold text-sm">{{ p.name }}</h4>
                            <div class="text-emerald-600 font-bold">R$ {{ Number(p.discount_price || p.price).toFixed(2)
                                }}</div>
                        </div>
                        <p class="text-xs text-slate-500 line-clamp-2 mt-1">{{ p.description }}</p>
                    </div>
                </div>
                <ProductCardSkeleton v-if="loading" v-for="n in 3" :key="'skl' + n" />
            </div>

            <!-- sentinel pro infinite scroll -->
            <div ref="sentinel" class="h-6"></div>
        </main>

        <!-- Modal full screen produto -->
        <ProductModalFull v-model:show="showModal" :product="activeProduct" :user="user" />

        <!--<FloatingWhatsApp :user="props.user" />-->
    </div>
</template>

<style scoped>
/* regras mobile-first leves */
</style>
