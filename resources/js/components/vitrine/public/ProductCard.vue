<!-- resources/js/components/vitrine/public/ProductCard.vue -->
<script setup lang="ts">
import { getIcon } from '@/lib/iconMap'
import { formatCurrency } from '@/lib/utils'

const props = defineProps({
  user: Object,
  product: { type: Object, required: true },
  viewMode: { type: String, default: 'grid' } // 'grid' ou 'list'
})

const emit = defineEmits(['open'])
function open() { emit('open', props.product) }
</script>

<template>
  <!-- MODO GRID -->
  <article
    v-if="viewMode === 'grid'"
    @click="open"
    class="bg-white rounded-xl p-3 shadow-sm hover:shadow-md transition cursor-pointer relative overflow-hidden"
  >
    <div v-if="product.featured"
      class="absolute top-3 left-3 text-white text-xs px-2 py-1 rounded-md flex items-center gap-1"
      :style="{ backgroundColor: props.user.theme_color }"
      >
      <component :is="getIcon('Star')" class="w-4 h-4" /> Destaque
    </div>

    <div class="flex items-center justify-center h-28 mb-3 rounded-lg">
      <img
        v-if="product.images?.length"
        :src="product.images[0].image || ('/' + product.images[0].image_path)"
        class="max-h-full object-contain"
        loading="lazy"
      />
      <div v-else class="text-slate-400">Sem imagem</div>
    </div>
    
    <h4 class="text-sm font-semibold text-slate-800">{{ product.name }}</h4>

    <div class="mt-2">
      <div class="text-slate-400 line-through text-xs" v-if="product.discount_price">
        {{ formatCurrency(product.price) }}
      </div>
      <div class="font-bold"
      :style="{ color: props.user.theme_color }"
      >
        {{ formatCurrency(product.discount_price || product.price) }}
      </div>
      <div class="text-xs text-slate-400">Estoque: {{ product.stock }}</div>
    </div>
  </article>

  <!-- MODO LISTA -->
  <article
    v-else
    @click="open"
    class="bg-white rounded-xl p-3 shadow-sm flex gap-3 items-center cursor-pointer"
  >
    <img
      v-if="product.images?.length"
      :src="product.images[0].image_base64 || ('/' + product.images[0].image_path)"
      class="w-20 h-20 object-cover rounded-lg"
      loading="lazy"
    />
    <div v-else class="w-20 h-20 flex items-center justify-center text-slate-400 bg-slate-100 rounded-lg">
      Sem imagem
    </div>

    <div class="flex-1">
      <div class="flex justify-between items-start">
        <h4 class="font-bold text-sm">{{ product.name }}</h4>

        <div class="text-emerald-600 font-bold">
          {{ formatCurrency(product.discount_price || product.price) }}
        </div>
      </div>

      <p class="text-xs text-slate-500 line-clamp-2 mt-1">
        {{ product.description }}
      </p>
    </div>
  </article>
</template>
