<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { getIcon } from '@/lib/iconMap'
import { formatCurrency } from '@/lib/utils'
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const props = defineProps({
  product: { type: Object, required: true },
  user: { type: Object, required: true },
  banners: { type: Array, default: () => [] },
  reviews: { type: Array, default: () => [] },
  isCustomerAuthenticated: { type: Boolean, default: false },
  favoriteProductIds: { type: Array as () => number[], default: () => [] },
  catalogMode: { type: String, default: 'store' },
})

const emit = defineEmits(['back', 'add-cart', 'toggle-favorite', 'open-customer-panel'])

const product = computed(() => props.product)
const activeIndex = ref(0)

watch(product, () => {
  activeIndex.value = 0
})

const images = computed(() => product.value?.images ?? [])

function buyNow() {
  const phone = (props.user?.whatsapp || '').replace(/\D/g, '') || ''
  const text = encodeURIComponent(
    `Ola, tenho interesse no produto: ${product.value?.name} (ID: ${product.value?.id}). Preco: ${
      product.value.discount_price ? formatCurrency(product.value.discount_price) : formatCurrency(product.value.price)
    }`,
  )

  const href = phone ? `https://wa.me/${phone}?text=${text}` : `https://wa.me/?text=${text}`
  window.open(href, '_blank')
}

const reviews = computed(() => props.reviews ?? [])
const reviewsPage = ref(1)
const reviewsLoading = ref(false)

function loadMoreReviews() {
  if (reviewsLoading.value) return
  reviewsLoading.value = true
  setTimeout(() => {
    reviewsPage.value++
    reviewsLoading.value = false
  }, 350)
}

function goBack() {
  emit('back')
}

const showForm = ref(false)
const form = ref({
  customer_name: '',
  product_id: product.value?.id,
  whatsapp: '',
  rating: 5,
  comment: '',
})

function toggleForm() {
  showForm.value = !showForm.value
}

function submitReview() {
  form.value.product_id = product.value.id

  router.post(
    route('vitrine.reviews.store', {
      slug: props.user.slug,
      product: product.value.id,
    }),
    form.value,
    {
      onSuccess: () => {
        form.value = {
          customer_name: '',
          whatsapp: '',
          rating: 5,
          comment: '',
        }
        alert('Avaliacao enviada com sucesso!')
      },
    },
  )
}

const isFavorite = computed(() => props.favoriteProductIds.includes(Number(product.value?.id)))

function toggleFavorite() {
  if (!props.isCustomerAuthenticated) {
    emit('open-customer-panel')
    return
  }

  emit('toggle-favorite', product.value)
}

function addToCart() {
  emit('add-cart', product.value)
}

const effectiveAction = computed(() => {
  const action = product.value?.conversion_type || 'cart'
  if (props.catalogMode === 'affiliate' && action === 'cart') return 'external'
  if (props.catalogMode === 'showcase' && action === 'cart') return 'whatsapp'
  return action
})

const ctaLabel = computed(() => product.value?.cta_label || ({
  cart: 'Adicionar ao carrinho',
  external: 'Ver oferta',
  whatsapp: 'Comprar via WhatsApp',
  lead: 'Quero saber mais',
}[effectiveAction.value] ?? 'Continuar'))

function convert() {
  if (effectiveAction.value === 'cart') return addToCart()
  if (effectiveAction.value === 'lead') return emit('open-customer-panel')
  if (effectiveAction.value === 'external') {
    if (product.value?.external_url) window.open(product.value.external_url, '_blank', 'noopener,noreferrer')
    return
  }
  buyNow()
}
</script>

<template>
  <div class="container-custom md:max-w-none w-full mx-auto min-h-screen bg-white md:min-h-0 md:bg-transparent md:shadow-none md:backdrop-blur-none">
    <div class="flex items-center justify-between p-3 border-b md:border-slate-200 md:bg-transparent">
      <button class="p-2 bg-white rounded-xl border shadow-sm md:rounded-none md:border-0 md:bg-transparent md:shadow-none" @click="goBack">
        <component :is="getIcon('ChevronLeft')" class="w-5 h-5" />
      </button>

      <div class="text-sm font-semibold">Detalhes do Produto</div>

      <button class="p-2 bg-white rounded-xl border shadow-sm md:rounded-none md:border-0 md:bg-transparent md:shadow-none" @click="toggleFavorite">
        <component :is="getIcon('Heart')" :fill="isFavorite ? 'var(--color-red-400)' : 'var(--color-slate-300)'" :class="['w-5 h-5', isFavorite ? 'text-red-500' : 'text-slate-400']" />
      </button>
    </div>

    <div class="p-4 md:p-6 grid gap-6 lg:grid-cols-[1.15fr_0.85fr]">
      <div>
        <div class="flex items-center justify-center" v-if="images.length">
          <div class="relative w-full h-60 md:h-[28rem] flex items-center justify-center rounded-2xl bg-slate-50 md:rounded-none md:bg-transparent md:border-0">
        <img :src="images[activeIndex]?.image || '/' + images[activeIndex]?.image_path" class="max-h-full object-contain" />
        <div class="absolute bottom-2 left-1/2 -translate-x-1/2 flex gap-2">
          <div v-for="(img, idx) in images" :key="idx" class="w-3 h-3 rounded-full cursor-pointer" :style="{ backgroundColor: props.user.theme_color, opacity: activeIndex === idx ? 1 : 0.3 }" @click="activeIndex = idx" />
            </div>
          </div>
        </div>

        <div class="pt-3" v-if="images.length">
          <div class="flex gap-2 overflow-x-auto">
            <div v-for="(img, i) in images" :key="i" class="p-1 rounded-lg cursor-pointer border" :style="{ borderColor: activeIndex === i ? props.user.theme_color : '#ccc' }" @click="activeIndex = i">
              <img :src="img.image || img.image_path" class="w-20 h-14 object-cover rounded-md" />
            </div>
          </div>
        </div>
      </div>

      <div class="space-y-4">
        <div class="rounded-2xl border p-4 bg-white lg:sticky lg:top-24 md:rounded-none md:border-0 md:bg-transparent md:shadow-none">
      <div class="flex justify-between items-center">
        <div>
          <h3 class="font-bold text-lg">{{ product?.name }}</h3>
        </div>

        <div>
          <div v-if="product?.discount_price > 0" class="line-through text-xs text-slate-400">{{ formatCurrency(product?.price) }}</div>
          <div class="font-bold text-lg" :style="{ color: props.user.theme_color }">{{ formatCurrency(product?.discount_price > 0 ? product?.discount_price : product?.price) }}</div>
        </div>
      </div>

      <p class="text-sm text-slate-600 whitespace-pre-line">{{ product?.description }}</p>

      <div class="flex items-center gap-3 text-xs text-slate-500">
        <div>Estoque: <b>{{ product?.stock ?? 0 }}</b></div>
        <div class="flex items-center gap-1">
          <component :is="getIcon('Star')" fill="var(--color-amber-200)" class="w-4 h-4 text-amber-400" />
          <span>{{ product?.rating ?? '-' }}</span>
        </div>
      </div>

      <div class="flex gap-3 mt-3">
        <button class="flex-1 text-white py-3 rounded-2xl font-semibold" :style="{ backgroundColor: props.user.theme_color }" @click="convert">
          <div class="flex justify-center items-center">
            <component :is="getIcon(effectiveAction === 'external' ? 'ExternalLink' : effectiveAction === 'whatsapp' ? 'MessageCircle' : effectiveAction === 'lead' ? 'UserRound' : 'ShoppingBag')" class="w-5 h-5 mr-2" />
            {{ ctaLabel }}
          </div>
        </button>

        <button v-if="effectiveAction !== 'cart' && ['store', 'hybrid'].includes(catalogMode)" class="p-3 bg-white border rounded-2xl text-slate-600" @click="addToCart">
          <component :is="getIcon('ShoppingCart')" class="w-5 h-5" />
        </button>
      </div>
        </div>
      </div>
    </div>

    <div class="px-4 md:px-6 pb-6">
      <div class="pt-2">
        <div class="flex gap-2">
          <button class="px-3 py-1 rounded-full bg-slate-100 text-sm">Avaliacoes ({{ reviews.length }})</button>
          <button class="bg-slate-600 text-white py-2 px-4 rounded-full font-semibold flex items-center" @click="toggleForm">
            <component :is="getIcon('UserStar')" class="w-5 h-5 mr-2" />
            {{ showForm ? 'Fechar' : 'Avaliar' }}
          </button>
        </div>

        <transition name="fade">
          <div v-if="showForm" class="mt-4 p-4 bg-slate-50 rounded-xl border md:rounded-none md:border-0 md:bg-transparent">
            <form @submit.prevent="submitReview">
              <input v-model="form.customer_name" type="text" placeholder="Seu nome" class="w-full border rounded-xl p-3 mb-2" />
              <input v-model="form.whatsapp" type="text" placeholder="Seu WhatsApp" class="w-full border rounded-xl p-3 mb-2" />

              <div class="flex gap-1">
                <component v-for="i in 5" :key="i" :is="getIcon(i <= form.rating ? 'Star' : 'StarOff')" :fill="i <= form.rating ? 'var(--color-amber-200)' : 'var(--color-slate-200)'" class="w-6 h-6 cursor-pointer" :class="i <= form.rating ? 'text-amber-400' : 'text-slate-400'" @click="form.rating = i" />
              </div>

              <textarea v-model="form.comment" rows="4" placeholder="Comentario" class="w-full border rounded-xl p-3 mt-3"></textarea>

              <button type="submit" class="w-full py-3 rounded-xl text-white font-semibold mt-3" :style="{ backgroundColor: props.user.theme_color }">Enviar Avaliacao</button>
            </form>
          </div>
        </transition>

        <div class="mt-3 space-y-3">
          <div v-for="r in reviews.slice(0, 5 * reviewsPage)" :key="r.id" class="p-3 bg-slate-50 rounded-xl md:rounded-none md:border-0 md:bg-transparent md:shadow-none">
            <div class="flex justify-between">
              <b>{{ r.customer_name }}</b>
              <small class="text-slate-400">{{ new Date(r.created_at).toLocaleDateString('pt-BR') }}</small>
            </div>

            <div class="flex gap-1 mt-1">
              <component v-for="n in 5" :key="n" :is="getIcon(n <= r.rating ? 'Star' : 'StarOff')" class="w-4 h-4" :fill="n <= r.rating ? 'var(--color-yellow-200)' : 'var(--color-slate-200)'" :class="n <= r.rating ? 'text-amber-400' : 'text-slate-400'" />
            </div>

            <p class="text-sm text-slate-700 mt-1">{{ r.comment }}</p>
          </div>

          <button class="w-full text-sm mt-2" :style="{ color: props.user.theme_color }" @click="loadMoreReviews">Carregar mais avaliacoes</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: all 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-5px);
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
