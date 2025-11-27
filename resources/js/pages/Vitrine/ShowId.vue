<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import ProductPageFull from '@/components/vitrine/public/ProductPageFull.vue'
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const props = defineProps({
  user: Object,
  settings: Object,
  pages: Array,
  products: Array,
  categories: Array,
  reviews: Array,
  page: Object,
  itemId: Number,
})

function goBackToProducts() {
  router.get(route('vitrine.public.page', {
    slug: props.user.slug,
    page: props.page.key,
  }))
}

const heroImage = props.settings?.hero_image ??
  "https://i.pinimg.com/1200x/9c/48/9e/9c489e9aa09653a6406a1945acfa8f60.jpg"
</script>

<template>
  <Head :title="props.settings?.site_title ?? props.user.business_name" />

  <div class="min-h-screen flex flex-col bg-slate-50 text-slate-900">
    <!-- CONTEÚDO: página de produto -->
    <main class="container mx-auto flex-grow">
      <ProductPageFull
        :products="props.products"
        :itemId="props.itemId"
        :user="props.user"
        @back="goBackToProducts"
      />
    </main>


    <footer class="bg-white border-t mt-6">
      <div class="container mx-auto px-4 py-6 text-sm text-slate-500 text-center">
        © {{ new Date().getFullYear() }} {{ props.user.business_name }}
      </div>
    </footer>
  </div>
</template>
