<!-- resources/js/components/vitrine/public/BottomNav.vue -->
<script setup lang="ts">
import { ref } from 'vue'
import { getIcon } from '@/lib/iconMap'
const props = defineProps({
  pages: { type: Array, default: () => [] } // opcional: usar pages para gerar ícones do menu
})
const active = ref('home')
function setTab(k) { active.value = k }
</script>

<template>
  <nav class="fixed bottom-4 left-4 right-4 z-40">
    <div class="bg-white/95 rounded-2xl px-6 py-3 flex justify-between items-center shadow-lg mx-auto max-w-xl">
      <button @click="setTab('home')" :class="active==='home' ? 'text-sky-600' : 'text-slate-400'">
        <component :is="getIcon('Home')" class="w-6 h-6" />
      </button>
      <button @click="setTab('search')" :class="active==='search' ? 'text-sky-600' : 'text-slate-400'">
        <component :is="getIcon('Search')" class="w-6 h-6" />
      </button>
      <button @click="setTab('cart')" :class="active==='cart' ? 'text-sky-600' : 'text-slate-400'">
        <component :is="getIcon('ShoppingCart')" class="w-6 h-6" />
      </button>
      <button @click="setTab('profile')" :class="active==='profile' ? 'text-sky-600' : 'text-slate-400'">
        <component :is="getIcon('User')" class="w-6 h-6" />
      </button>
    </div>
  </nav>
</template>
