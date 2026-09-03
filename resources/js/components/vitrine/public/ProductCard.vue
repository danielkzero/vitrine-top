<!-- resources/js/components/vitrine/public/ProductCard.vue -->
<script setup lang="ts">
import { getIcon } from '@/lib/iconMap'
import { formatCurrency } from '@/lib/utils'
import { computed } from 'vue'

const props = defineProps({
  user: Object,
  product: { type: Object, required: true },
  viewMode: { type: String, default: 'grid' },
  favoriteProductIds: { type: Array as () => number[], default: () => [] },
  canFavorite: { type: Boolean, default: false },
  catalogMode: { type: String, default: 'store' },
})

const emit = defineEmits(['open', 'add-cart', 'toggle-favorite', 'lead'])

function open() {
  emit('open', props.product)
}

function addToCart(event: Event) {
  event.stopPropagation()
  emit('add-cart', props.product)
}

const effectiveAction = computed(() => {
  const action = props.product?.conversion_type || 'cart'
  if (props.catalogMode === 'affiliate' && action === 'cart') return 'external'
  if (props.catalogMode === 'showcase' && action === 'cart') return 'whatsapp'
  return action
})

const ctaLabel = computed(() => props.product?.cta_label || ({
  cart: 'Adicionar ao carrinho',
  external: 'Ver oferta',
  whatsapp: 'Falar no WhatsApp',
  lead: 'Quero saber mais',
}[effectiveAction.value] ?? 'Ver produto'))

function convert(event: Event) {
  event.stopPropagation()

  if (effectiveAction.value === 'cart') return emit('add-cart', props.product)
  if (effectiveAction.value === 'lead') return emit('lead', props.product)

  if (effectiveAction.value === 'external') {
    if (props.product?.external_url) window.open(props.product.external_url, '_blank', 'noopener,noreferrer')
    else emit('open', props.product)
    return
  }

  const phone = String(props.user?.whatsapp || '').replace(/\D/g, '')
  const text = encodeURIComponent(`Olá, tenho interesse em ${props.product?.name}.`)
  window.open(phone ? `https://wa.me/${phone}?text=${text}` : `https://wa.me/?text=${text}`, '_blank', 'noopener,noreferrer')
}

function toggleFavorite(event: Event) {
  event.stopPropagation()
  emit('toggle-favorite', props.product)
}

const isFavorite = computed(() => props.favoriteProductIds.includes(Number(props.product?.id)))
</script>

<template>
  <article
    v-if="viewMode === 'grid'"
    @click="open"
    class="relative flex h-full flex-col overflow-hidden rounded-xl bg-white p-3 shadow-sm transition hover:shadow-md cursor-pointer md:rounded-xl md:border md:border-slate-200/80 md:bg-white md:p-3 md:shadow-[0_14px_28px_rgba(15,23,42,0.08)] md:hover:shadow-[0_20px_36px_rgba(15,23,42,0.12)]"
  >
    <div v-if="product.featured" class="absolute top-2 left-2 z-20 bg-amber-100/80 text-xs px-2 py-1 rounded-md flex items-center gap-1" :style="{ color: props.user?.theme_color }">
      <component :is="getIcon('Star')" class="w-4 h-4" /> Destaque
    </div>

    <button class="absolute top-2 right-2 z-20" @click="toggleFavorite">
      <component :is="getIcon('Heart')" :fill="isFavorite ? 'var(--color-red-400)' : 'var(--color-slate-300)'" :class="['w-5 h-5', isFavorite ? 'text-red-500' : 'text-slate-400']" />
    </button>

    <div class="flex items-center justify-center h-28 mb-3 rounded-lg md:h-40 md:rounded-lg md:bg-slate-50" v-if="product.images?.length">
      <img :src="product.images[0].image || product.images[0].image_path" class="max-h-full object-contain md:scale-[1.02]" loading="lazy" />
    </div>

    <h4 class="text-sm font-semibold text-slate-800">{{ product.name }}</h4>
    <p class="text-xs text-slate-500 line-clamp-2 mt-1">{{ product.description }}</p>
    <div class="mt-2">
      <div class="text-slate-400 line-through text-xs" v-if="product.discount_price > 0">{{ formatCurrency(product.price) }}</div>
      <div class="font-bold" :style="{ color: props.user?.theme_color }">{{ formatCurrency(product.discount_price > 0 ? product.discount_price : product.price) }}</div>
      <div class="text-xs text-slate-400" v-if="product.stock">Estoque: {{ product.stock }}</div>
    </div>

    <div class="mt-auto pt-3">
      <button class="w-full rounded-lg py-2 text-sm text-white md:rounded-lg md:py-2.5 md:font-medium" :style="{ backgroundColor: props.user?.theme_color }" @click="convert">
        {{ ctaLabel }}
      </button>
    </div>
  </article>

  <article v-else @click="open" class="bg-white rounded-xl p-3 shadow-sm flex gap-3 items-center cursor-pointer relative md:rounded-xl md:border md:border-slate-200/80 md:bg-white md:p-4 md:shadow-[0_12px_24px_rgba(15,23,42,0.08)]">
    <img v-if="product.images?.length" :src="product.images[0].image || product.images[0].image_path" class="w-20 h-20 object-cover rounded-lg md:w-28 md:h-28 md:rounded-lg" loading="lazy" />

    <div class="flex-1">
      <div class="flex justify-between items-start">
        <div class="mx-2">
          <h4 class="font-bold text-sm">{{ product.name }}</h4>
          <p class="text-xs text-slate-500 line-clamp-2 mt-1">{{ product.description }}</p>
          <div v-if="product.featured" class="max-w-24 bg-amber-100 text-xs px-2 py-1 rounded-md flex items-center gap-1" :style="{ color: props.user?.theme_color }">
            <component :is="getIcon('Star')" class="w-4 h-4" /> Destaque
          </div>
        </div>
        <div>
          <button class="mb-1" @click="toggleFavorite">
            <component :is="getIcon('Heart')" :fill="isFavorite ? 'var(--color-red-400)' : 'var(--color-slate-300)'" :class="['w-4 h-4', isFavorite ? 'text-red-500' : 'text-slate-400']" />
          </button>
          <div class="text-slate-400 line-through text-xs" v-if="product.discount_price > 0">{{ formatCurrency(product.price) }}</div>
          <div class="font-bold" :style="{ color: props.user?.theme_color }">{{ formatCurrency(product.discount_price > 0 ? product.discount_price : product.price) }}</div>
          <div class="text-xs text-slate-400 whitespace-nowrap" v-if="product.stock">Estoque: {{ product.stock }}</div>
        </div>
      </div>
      <button class="mt-2 rounded-lg border px-3 py-1.5 text-xs md:rounded-lg md:px-4 md:py-2" @click="convert">{{ ctaLabel }}</button>
    </div>
  </article>
</template>
