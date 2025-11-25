<!-- resources/js/components/vitrine/public/ProductCard.vue -->
<script setup lang="ts">
import { computed } from 'vue'
import { getIcon } from '@/lib/iconMap'
const props = defineProps({
  product: { type: Object, required: true }
})
const emit = defineEmits(['open'])
const price = computed(() => `R$ ${Number(props.product.discount_price || props.product.price || 0).toFixed(2)}`)
function open() { emit('open', props.product) }
</script>

<template>
  <article @click="open" class="bg-white rounded-xl p-3 shadow-sm hover:shadow-md transition cursor-pointer relative overflow-hidden">
    <div v-if="product.featured" class="absolute top-3 left-3 bg-emerald-600 text-white text-xs px-2 py-1 rounded-md flex items-center gap-1">
      <component :is="getIcon('Star')" class="w-4 h-4" /> Destaque
    </div>
    <div class="flex items-center justify-center h-28 mb-3 bg-slate-50 rounded-lg">
      <img v-if="product.images?.length" :src="product.images[0].image_base64 || ('/' + product.images[0].image_path)" class="max-h-full object-contain" loading="lazy" />
      <div v-else class="text-slate-400">Sem imagem</div>
    </div>
    <h4 class="text-sm font-semibold text-slate-800">{{ product.name }}</h4>
    <div class="flex justify-between items-center mt-2">
      <div class="text-emerald-600 font-bold">{{ price }}</div>
      <div class="text-xs text-slate-400">Estoque: {{ product.stock }}</div>
    </div>
  </article>
</template>
