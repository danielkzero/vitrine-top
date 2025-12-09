<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import ProductPageFull from '@/components/vitrine/public/ProductPageFull.vue'
import { publicUser, loadPublicUser } from "@/composables/usePublicUser"
import LoadingPage from './LoadingPage.vue'

/* ===========================================================
   1) PEGAR SLUG, PAGE E ID DA URL REAL
   =========================================================== */
const path = window.location.pathname.split('/').filter(Boolean)

// /capware/catalogo/10 -> ["capware","catalogo","10"]
const slug = path[0]
const pageKey = path[1] ?? null
const itemId = Number(path[2]) ?? null

/* ===========================================================
   2) STATES
   =========================================================== */
const user = ref<any>(null)
const pages = ref<any[]>([])
const currentPage = ref<any>(null)

const product = ref(null)
const banners = ref([])
const reviews = ref([])


/* ===========================================================
   3) CARREGAR USER
   =========================================================== */
async function loadUser() {
  const response = await axios.get(`/v1/users/${slug}`)
  user.value = response.data?.data ?? response.data
}

/* ===========================================================
   4) CARREGAR PÁGINAS
   =========================================================== */
async function loadPages() {
  const response = await axios.get(`/v1/users/${slug}/pages`)

  const list = Array.isArray(response.data)
    ? response.data
    : response.data.data ?? []

  pages.value = list
  currentPage.value =
    list.find((p: any) => p.key === pageKey) ?? list[0] ?? null
}

/* ===========================================================
   5) CARREGAR PRODUTO ESPECÍFICO
   =========================================================== */
async function loadProduct() {
  const response = await axios.get(`/v1/users/${slug}/products/${itemId}`)
  product.value = response.data?.data ?? response.data
}

/* ===========================================================
   6) OUTROS DADOS
   =========================================================== */
async function loadBanners() {
  const response = await axios.get(`/v1/users/${slug}/banners`)
  banners.value = response.data?.data ?? response.data
}

async function loadReviews() {
  const response = await axios.get(`/v1/users/${slug}/reviews`)
  reviews.value = response.data?.data ?? response.data
}

/* ===========================================================
   7) NAVEGAR DE VOLTA PARA A LISTA
   =========================================================== */
function goBackToProducts() {
  router.get(
    route("vitrine.public.page", {
      slug,
      page: currentPage.value.key,
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
  await loadProduct()
  await loadBanners()
  await loadReviews()
})


const load = publicUser

/* ===========================================================
   9) HEAD
   =========================================================== */
const title = computed(() =>
  product.value?.name
    ? `${product.value.name} · ${user.value?.business_name}`
    : user.value?.business_name
)
</script>

<template>

  <Head :title="user?.business_name">
    <link rel="icon" type="image/png" :href="user?.logo_path || ''" />
  </Head>

  <LoadingPage :user="user" v-model:load="load" v-if="load && !user" />



  <div v-else class="min-h-screen flex flex-col bg-slate-50 text-slate-900">

    <main class="container-custom mx-auto flex-grow">
      <ProductPageFull :product="product" :banners="banners" :reviews="reviews" :user="user" @back="goBackToProducts" />
    </main>

    <footer class="bg-white border-t mt-6">
      <div class="container-custom mx-auto px-4 py-6 text-sm text-slate-500 text-center">
        © {{ new Date().getFullYear() }} {{ user.business_name }}
      </div>
    </footer>

  </div>
</template>
