<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import SimplePublic from '@/components/vitrine/public/SimplePublic.vue'
import LinksPublic from '@/components/vitrine/public/LinksPublic.vue'
import GalleryPublic from '@/components/vitrine/public/GalleryPublic.vue'
import ReviewsPublic from '@/components/vitrine/public/ReviewsPublic.vue'
import ProductsPublic from '@/components/vitrine/public/ProductsPublic.vue'

const props = defineProps({
  user: Object,
  settings: Object,
  pages: Array,
  products: Array,
  categories: Array,
  reviews: Array
})

function resolvePageComponent(page) {
  return {
    simple: SimplePublic,
    links: LinksPublic,
    gallery: GalleryPublic,
    reviews: ReviewsPublic,
    products: ProductsPublic
  }[page.type] || SimplePublic
}

const heroImage = props.settings?.hero_image ?? "https://www.shutterstock.com/image-vector/online-shopping-3d-mobile-application-260nw-1712162656.jpg"
</script>

<template>
  <Head :title="props.settings?.site_title ?? props.user.business_name" />

  <div class="min-h-screen bg-slate-50 text-slate-900">

    <!-- ============================ -->
    <!-- HERO COM BACKGROUND IMAGE -->
    <!-- ============================ -->
    <header 
      class="relative w-full h-[300px] md:h-[380px] flex items-center justify-center bg-cover bg-center bg-no-repeat"
      :style="{ backgroundImage: `url('${heroImage}')` }"
    >
      <!-- Overlay escuro para contraste -->
      <div class="absolute inset-0 bg-black/40 backdrop-blur-[1px]"></div>

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
    <main class="container mx-auto px-2 py-10 space-y-12">
      <section v-for="page in props.pages" :key="page.id">
        <div class="mb-4 flex items-center justify-between">
          <h2 class="text-xl font-bold">{{ page.title }}</h2>
        </div>

        <component
          :is="resolvePageComponent(page)"
          :page="page"
          :products="props.products"
          :categories="props.categories"
          :reviews="props.reviews"
        />
      </section>
    </main>

    <!-- ============================ -->
    <!-- FOOTER -->
    <!-- ============================ -->
    <footer class="bg-white border-t">
      <div class="container mx-auto px-4 py-6 text-sm text-slate-500 text-center">
        © {{ new Date().getFullYear() }} {{ props.user.business_name }} — feito com ❤️
      </div>
    </footer>
  </div>
</template>
