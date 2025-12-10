<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import SimplePublic from '@/components/vitrine/public/SimplePublic.vue'
import LinksPublic from '@/components/vitrine/public/LinksPublic.vue'
import GalleryPublic from '@/components/vitrine/public/GalleryPublic.vue'
import ReviewsPublic from '@/components/vitrine/public/ReviewsPublic.vue'
import ProductsPublic from '@/components/vitrine/public/ProductsPublic.vue'
import BottomNav from '@/components/vitrine/public/BottomNav.vue'
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { getIcon } from '@/lib/iconMap'
import { publicUser, loadPublicUser } from "@/composables/usePublicUser"
import LoadingPage from './LoadingPage.vue'
import BaseButton from '@/components/ui/button/BaseButton.vue';

/* ===========================================================
   1) PEGAR SLUG E PAGE DA URL REAL
   =========================================================== */
const path = window.location.pathname.split('/').filter(Boolean)

// /capware           -> ["capware"]
// /capware/catalogo  -> ["capware", "catalogo"]
const slug = path[0]
const pageKey = path[1] ?? null

/* ===========================================================
   2) STATES DA PÁGINA
   =========================================================== */
const user = ref<any>(null)
const pages = ref<any[]>([])
const currentPage = ref<any>(null)

const products = ref([])
const categories = ref([])
const reviews = ref([])
const banners = ref([])

/* ===========================================================
   3) CARREGAR API DO USUÁRIO
   =========================================================== */
async function loadUser() {
  const { data } = await axios.get(`/v1/users/${slug}`)
  user.value = data.data
}

/* ===========================================================
   4) CARREGAR PÁGINAS
   =========================================================== */
async function loadPages() {
  const response = await axios.get(`/v1/users/${slug}/pages`)

  // garante que pages seja SEMPRE um array
  const list = Array.isArray(response.data)
    ? response.data
    : response.data.data ?? [] // caso seja paginado

  pages.value = list

  currentPage.value =
    list.find((p: any) => p.key === pageKey) ?? list[0]
}

/* ===========================================================
   5) CARREGAR DEPENDÊNCIAS DA PÁGINA
   =========================================================== */
async function loadDependencies() {

  if (!currentPage.value) return


  if (currentPage.value.type === 'products') {
    banners.value = (await axios.get(`/v1/users/${slug}/banners`)).data?.data
    products.value = (await axios.get(`/v1/users/${slug}/products`)).data?.data
    categories.value = (await axios.get(`/v1/users/${slug}/categories`)).data?.data
  }

  if (currentPage.value.type === 'reviews') {
    reviews.value = (await axios.get(`/v1/users/${slug}/reviews`)).data?.data
  }
}

/* ===========================================================
   6) COMPOSIÇÃO DOS COMPONENTES POR TIPO
   =========================================================== */
function resolvePageComponent(page: any) {
  return {
    simple: SimplePublic,
    links: LinksPublic,
    gallery: GalleryPublic,
    reviews: ReviewsPublic,
    products: ProductsPublic
  }[page.type] || SimplePublic
}

const pageLocal = computed(() => currentPage.value)
const heroImage = computed(() => user.value?.background_path ?? '')

/* ===========================================================
   7) NAVEGAÇÃO ENTRE PÁGINAS (ZIGGY + INERTIA)
   =========================================================== */
function goToPage(page: any) {
  router.get(
    route('vitrine.public.page', {
      slug,
      page: page.key
    })
  )
}

/* ===========================================================
   8) CARREGAMENTO INICIAL
   =========================================================== */
onMounted(async () => {
  await loadPublicUser(slug)
  await loadUser()

  await loadPages()
  await loadDependencies()
})

const load = publicUser
</script>

<template>

  <Head :title="user?.business_name">
    <link rel="icon" type="image/png" :href="user?.logo_path || ''" />
  </Head>

  <LoadingPage :user="user" v-model:load="load" v-if="load && !user" />


  <div v-else class="min-h-screen flex flex-col bg-slate-50 text-slate-900">

    <!-- HERO -->
    <header
      class="container-custom mx-auto relative w-full h-[10rem] md:h-[12rem] flex items-center justify-center bg-cover bg-center bg-no-repeat rounded-b-2xl"
      :style="{ backgroundImage: `url('${heroImage}')` }">

      <div class="absolute inset-0 bg-black/40 backdrop-blur-[1px] rounded-b-2xl"></div>

      <div class="relative z-10 text-center text-white px-4">
        <h1 class="text-4xl md:text-5xl font-extrabold drop-shadow-lg items-center flex">
          <img :src="user.logo_path" class="w-14 mr-3 rounded-2xl bg-amber-50 shadow" />
          {{ user.business_name }}
        </h1>
        <p class="mt-3 text-lg opacity-90">
          {{ user.subtitle ?? '' }}
        </p>
      </div>
    </header>

    <!-- CONTEÚDO -->
    <main class="container-custom mx-auto px-3 flex-grow">
      <section>

        <h2 class="text-xl font-bold items-center flex py-6">
          <component :is="getIcon(pageLocal?.icon)" class="mr-2" />
          {{ pageLocal?.title }}
        </h2>

        <!-- RENDER DO COMPONENTE DINÂMICO -->
        <component v-if="pageLocal" :is="resolvePageComponent(pageLocal)" :user="user" :page="pageLocal"
          :products="products" :categories="categories" :reviews="reviews" :banners="banners" />

      </section>
    </main>

    <BottomNav :user="user" :pages="pages" :activeKey="pageLocal?.key" @navigate="goToPage" />

    <!-- FOOTER -->
    <footer class="bg-white border-t pb-24 mt-6">
      <div class="container-custom mx-auto px-4 py-6 text-sm text-slate-500">
        <h2 class="text-xl md:text-xl font-extrabold drop-shadow-lg items-center flex mb-3">Sobre</h2>
        {{ user.description }}
      </div>
      <div class="container-custom mx-auto px-4 py-6 text-sm text-slate-500 text-center">
        © {{ new Date().getFullYear() }} {{ user.business_name }}
        <div class="container-custom mx-auto px-4 text-xs mt-2 text-slate-500 text-center">
          Desenvolvido por
          <a href="https://vitrinetop.hydradigital.com.br" target="_blank" class="font-extrabold">vitrine.top</a>
        </div>
      </div>
    </footer>

  </div>
</template>
