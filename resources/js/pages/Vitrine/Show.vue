<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import SimplePublic from '@/components/vitrine/public/SimplePublic.vue'
import LinksPublic from '@/components/vitrine/public/LinksPublic.vue'
import GalleryPublic from '@/components/vitrine/public/GalleryPublic.vue'
import ReviewsPublic from '@/components/vitrine/public/ReviewsPublic.vue'
import ProductsPublic from '@/components/vitrine/public/ProductsPublic.vue'
import BottomNav from '@/components/vitrine/public/BottomNav.vue'
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { computed, ref } from 'vue'
import { getIcon } from '@/lib/iconMap'

const props = defineProps({
  user: Object,
  settings: Object,
  pages: Array,
  products: Array,
  categories: Array,
  reviews: Array,
  page: {
    type: Object as () => Page,
    required: true
  }
})

const pageLocal = computed(() => props.page)

function resolvePageComponent(page: any) {
  return {
    simple: SimplePublic,
    links: LinksPublic,
    gallery: GalleryPublic,
    reviews: ReviewsPublic,
    products: ProductsPublic
  }[page.type] || SimplePublic
}

function goToPage(page: Page) {
  router.get(
    route('vitrine.public', { slug: props.user.slug }),
    {
      p: page.key,
      preserveScroll: true,
    }
  )
}

interface Page {
  id: number
  icon: string
  key: string
  type: string
  title: string
  is_active: boolean
  order: number
}

const heroImage = props.settings?.hero_image ?? "https://i.pinimg.com/1200x/9c/48/9e/9c489e9aa09653a6406a1945acfa8f60.jpg"
</script>


<template>

  <Head :title="props.settings?.site_title ?? props.user.business_name" />

  <div class="min-h-screen flex flex-col bg-slate-50 text-slate-900">

    <!-- ============================ -->
    <!-- HERO COM BACKGROUND IMAGE -->
    <!-- ============================ -->
    <header
      class="container mx-auto relative w-full h-[150px] md:h-[380px] flex items-center justify-center bg-cover bg-center bg-no-repeat rounded-b-2xl"
      :style="{ backgroundImage: `url('${heroImage}')` }">
      
      <!-- Overlay escuro para contraste -->
      <div class="absolute inset-0 bg-black/40 backdrop-blur-[1px] rounded-b-2xl"></div>

      <!-- Conteúdo -->
      <div class="relative z-10 text-center text-white px-4">
        <h1 class="text-4xl md:text-5xl font-extrabold drop-shadow-lg">
          {{ props.user.business_name }}
        </h1>
        <p class="mt-3 text-lg opacity-90">
          {{ props.settings?.subtitle ?? 'Bem-vindo à nossa vitrine' }}
        </p>
      </div>
    </header>

    <!-- ============================ -->
    <!-- CONTEÚDO DA VITRINE -->
    <!-- ============================ -->
    <main class="container mx-auto px-3 flex-grow">
      <section>
        <h2 class="text-xl font-bold items-center flex py-6">
          <component :is="getIcon(pageLocal.icon)" class="mr-2" />
          {{ pageLocal.title }}
        </h2>

        <component :is="resolvePageComponent(pageLocal)" :page="pageLocal" :products="props.products"
          :categories="props.categories" :reviews="props.reviews" />
      </section>
    </main>

    <BottomNav :pages="props.pages" :activeKey="pageLocal.key" @navigate="goToPage" />
    
    <!-- ============================ -->
    <!-- FOOTER -->
    <!-- ============================ -->
    <footer class="bg-white border-t pb-24 mt-6">
      <div class="container mx-auto px-4 py-6 text-sm text-slate-500 text-center">
        © {{ new Date().getFullYear() }} {{ props.user.business_name }}
      </div>
    </footer>
  </div>
</template>
