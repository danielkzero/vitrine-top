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
import CategorySkeleton from './CategorySkeleton.vue'

const props = defineProps({
  page: Object,
  products: Array,
  categories: Array,
  user: Object,
  settings: Object,
  banners: Object,
  cartCount: { type: Number, default: 0 },
  favoriteProductIds: { type: Array as () => number[], default: () => [] },
  isCustomerAuthenticated: { type: Boolean, default: false },
})

const emit = defineEmits(['open-cart', 'open-customer-panel', 'add-cart', 'toggle-favorite'])

const q = ref('')
const selectedCategory = ref(props.page?.category_id ?? null)
const viewMode = ref<'grid' | 'list'>('grid')
const perPage = ref(12)
const loading = ref(true)
const catalogMode = computed(() => props.page?.catalog_mode || 'store')
const cartEnabled = computed(() => ['store', 'hybrid'].includes(catalogMode.value))

const publicProducts = computed(() => {
  let items = (props.products ?? []).filter((p: any) => !!p.is_public)

  if (selectedCategory.value) {
    items = items.filter((p: any) => p.category?.id === selectedCategory.value)
  }

  if (q.value && q.value.trim().length) {
    const term = q.value.toLowerCase()
    items = items.filter((p: any) => (p.name || '').toLowerCase().includes(term) || (p.description || '').toLowerCase().includes(term))
  }

  items.sort((a: any, b: any) => Number(b.featured) - Number(a.featured) || (b.id - a.id))
  return items
})

const visibleCount = ref(perPage.value)
const visibleProducts = computed(() => publicProducts.value.slice(0, visibleCount.value))

function loadMore() {
  if (visibleCount.value >= publicProducts.value.length) return
  loading.value = true
  setTimeout(() => {
    visibleCount.value = Math.min(publicProducts.value.length, visibleCount.value + perPage.value)
    loading.value = false
  }, 350)
}

const onSearch = debounce(() => {
  visibleCount.value = perPage.value
}, 300)

watch(q, () => onSearch())

function openProduct(prod: any) {
  router.get(
    route('vitrine.public.page.id', {
      slug: props.user.slug,
      page: props.page.key,
      id: prod.id,
    }),
  )
}

function handleCartClick() {
  emit('open-cart')
}

function handleAddCart(product: any) {
  emit('add-cart', product)
}

function handleToggleFavorite(product: any) {
  if (!props.isCustomerAuthenticated) {
    emit('open-customer-panel')
    return
  }

  emit('toggle-favorite', product)
}

function handleLead() {
  emit('open-customer-panel')
}

const sentinel = ref<HTMLElement | null>(null)

onMounted(() => {
  const io = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) loadMore()
      })
    },
    { root: null, rootMargin: '200px', threshold: 0.1 },
  )

  if (sentinel.value) io.observe(sentinel.value)
})

watch(
  () => props.products,
  (products) => {
    if (Array.isArray(products) && products.length >= 0) {
      loading.value = false
    }
  },
  { immediate: true },
)

const STORAGE_KEY = 'vitrine_view_mode'

onMounted(() => {
  const saved = localStorage.getItem(STORAGE_KEY)
  if (saved === 'grid' || saved === 'list') viewMode.value = saved
})

function toggleViewMode() {
  viewMode.value = viewMode.value === 'grid' ? 'list' : 'grid'
  localStorage.setItem(STORAGE_KEY, viewMode.value)
}
</script>

<template>
  <div class="text-slate-900">
    <section v-if="catalogMode === 'presell' && page?.content"
      class="prose mb-6 max-w-none rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
      v-html="page.content">
    </section>

    <div v-if="catalogMode === 'affiliate'" class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
      Alguns links desta página podem gerar comissão para o vendedor, sem custo adicional para você.
    </div>

    <header class="pb-4 lg:hidden">
      <div class="flex items-center gap-3">
        <button v-if="cartEnabled" class="p-2 bg-white rounded-xl shadow-sm border border-slate-100 relative md:rounded-none md:shadow-none md:border-0 md:bg-transparent" @click="handleCartClick">
          <component :is="getIcon('ShoppingCart')" fill="currentColor" class="w-5 h-5 text-slate-700" />
          <span v-if="cartCount > 0" class="absolute -right-1 -top-1 min-w-5 h-5 px-1 rounded-full bg-rose-600 text-white text-[10px] flex items-center justify-center">{{ cartCount }}</span>
        </button>

        <div class="flex-1 relative">
          <button class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
            <component :is="getIcon('Search')" class="w-4 h-4" />
          </button>
          <input v-model="q" type="search" placeholder="Pesquisar..." class="w-full pl-10 pr-3 py-2 rounded-xl bg-white border border-slate-100 focus:ring-0 text-sm md:rounded-none md:border-0 md:bg-transparent md:shadow-none" />
          <button v-if="q" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400" @click="q = ''">
            <component :is="getIcon('X')" class="w-4 h-4" />
          </button>
        </div>

        <button type="button" class="p-2 bg-white rounded-xl shadow-sm border border-slate-100 ml-2 md:rounded-none md:shadow-none md:border-0 md:bg-transparent" @click="toggleViewMode">
          <component :is="getIcon(viewMode === 'grid' ? 'LayoutGrid' : 'List')" class="w-5 h-5 text-slate-700" />
        </button>
      </div>

      <BannerCarousel :images="props.banners" v-if="props.banners.length" />

      <h2 class="text-xl font-bold items-center flex my-6 md:text-2xl">Categorias</h2>

      <div class="mt-3 overflow-x-auto pb-2 lg:hidden">
        <div class="flex gap-2">
          <button :class="['px-3 py-1.5 rounded-xl text-sm whitespace-nowrap', !selectedCategory ? 'text-white' : 'bg-white border']" :style="!selectedCategory ? { backgroundColor: props.user.theme_color } : {}" @click="selectedCategory = null">Todas</button>

          <button v-for="c in categories" :key="c.id" :class="['px-3 py-1.5 rounded-xl text-sm whitespace-nowrap', selectedCategory === c.id ? 'text-white' : 'bg-white border']" :style="selectedCategory === c.id ? { backgroundColor: props.user.theme_color } : {}" @click="selectedCategory = c.id">
            {{ c.name }}
          </button>

          <template v-if="categories?.length === 0">
            <CategorySkeleton v-for="n in 3" :key="'cat-sk' + n" />
          </template>
        </div>
      </div>
    </header>

    <main class="pb-2 lg:grid lg:grid-cols-[280px_minmax(0,1fr)] lg:gap-8">
      <aside class="hidden lg:block">
        <div class="sticky top-20 space-y-5">
          <div v-if="cartEnabled">
            <button
              class="w-full flex items-center justify-between border border-slate-200 bg-white px-4 py-3 rounded-2xl text-sm font-semibold text-slate-800 shadow-sm hover:shadow-md transition"
              @click="handleCartClick"
            >
              <span class="flex items-center gap-2">
                <component :is="getIcon('ShoppingCart')" class="w-4 h-4" />
                Carrinho
              </span>
              <span class="inline-flex min-w-6 h-6 items-center justify-center rounded-full bg-slate-900 text-white text-xs px-2">
                {{ cartCount }}
              </span>
            </button>
          </div>

          <div class="space-y-3">
            <div class="relative">
              <button class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                <component :is="getIcon('Search')" class="w-4 h-4" />
              </button>
              <input
                v-model="q"
                type="search"
                placeholder="Pesquisar produto"
                class="w-full pl-10 pr-10 py-2.5 border border-slate-200 rounded-xl bg-white text-sm focus:outline-none focus:ring-2 focus:ring-slate-300"
              />
              <button v-if="q" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400" @click="q = ''">
                <component :is="getIcon('X')" class="w-4 h-4" />
              </button>
            </div>

            <button
              type="button"
              class="w-full flex items-center justify-center gap-2 border border-slate-200 rounded-xl bg-white px-3 py-2.5 text-sm text-slate-700 hover:text-slate-900 hover:border-slate-300 transition"
              @click="toggleViewMode"
            >
              <component :is="getIcon(viewMode === 'grid' ? 'LayoutGrid' : 'List')" class="w-4 h-4" />
              {{ viewMode === 'grid' ? 'Visualização em grade' : 'Visualização em lista' }}
            </button>
          </div>

          <div class="space-y-2">
            <h3 class="text-sm font-semibold text-slate-800 uppercase tracking-wide">Categorias</h3>
            <div class="space-y-1 border-l border-slate-200 pl-3">
              <button
                class="w-full text-left px-2 py-2 text-sm transition"
                :class="!selectedCategory ? 'font-semibold text-slate-900' : 'text-slate-600 hover:text-slate-900'"
                @click="selectedCategory = null"
              >
                Todas
              </button>
              <button
                v-for="c in categories"
                :key="c.id"
                class="w-full text-left px-2 py-2 text-sm transition"
                :class="selectedCategory === c.id ? 'font-semibold text-slate-900' : 'text-slate-600 hover:text-slate-900'"
                @click="selectedCategory = c.id"
              >
                {{ c.name }}
              </button>
            </div>
          </div>
        </div>
      </aside>

      <section>
      <h2 class="hidden lg:flex text-xl font-bold items-center py-4 md:py-2 md:text-2xl">
        <component :is="getIcon(page?.icon)" class="mr-2" />
        {{ page?.title }}
      </h2>

      <div class="hidden lg:block">
        <BannerCarousel :images="props.banners" v-if="props.banners.length" />
      </div>

      <div class="hidden lg:flex items-center justify-end mt-3 mb-4">
        <span class="text-sm text-slate-500">{{ publicProducts.length }} itens</span>
      </div>

      <div v-if="!visibleProducts.length && !loading" class="text-center py-12 text-slate-400">Nenhum produto disponivel.</div>

      <div v-if="viewMode === 'grid'" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-3 lg:gap-6">
        <ProductCard
          v-for="p in visibleProducts"
          :key="p.id"
          :product="p"
          :user="props.user"
          :favorite-product-ids="favoriteProductIds"
          :can-favorite="isCustomerAuthenticated"
          :catalog-mode="catalogMode"
          viewMode="grid"
          @open="openProduct"
          @add-cart="handleAddCart"
          @toggle-favorite="handleToggleFavorite"
          @lead="handleLead"
        />
        <ProductCardSkeleton v-if="loading" v-for="n in 6" :key="'sk' + n" viewMode="grid" />
      </div>

      <div v-else class="space-y-3 lg:space-y-4">
        <ProductCard
          v-for="p in visibleProducts"
          :key="p.id"
          :product="p"
          :user="props.user"
          :favorite-product-ids="favoriteProductIds"
          :can-favorite="isCustomerAuthenticated"
          :catalog-mode="catalogMode"
          viewMode="list"
          @open="openProduct"
          @add-cart="handleAddCart"
          @toggle-favorite="handleToggleFavorite"
          @lead="handleLead"
        />
        <ProductCardSkeleton v-if="loading" v-for="n in 3" :key="'skl' + n" viewMode="list" />
      </div>

      <div ref="sentinel" class="h-6"></div>
      </section>
    </main>
  </div>
</template>
