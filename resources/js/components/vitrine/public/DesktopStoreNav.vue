<script setup lang="ts">
import { getIcon } from '@/lib/iconMap'

defineProps<{
  pages: Array<any>
  activeKey?: string | null
  themeColor?: string | null
  isAuthenticated?: boolean
  cartCount?: number
  whatsappUrl?: string | null
}>()

const emit = defineEmits<{
  (e: 'navigate', key: string): void
  (e: 'open-customer'): void
  (e: 'open-cart'): void
}>()
</script>

<template>
  <section class="hidden md:block sticky top-0 z-40 w-full border-b border-slate-200/80 bg-white/85 backdrop-blur-xl">
    <div class="container-custom px-6 lg:px-10 py-2.5 flex items-center justify-between gap-4">
      <div class="flex items-center gap-1 rounded-full border border-slate-200 bg-white px-2 py-1 shadow-sm">
        <button
          v-for="p in pages"
          :key="p.key"
          class="px-4 py-1.5 rounded-full text-sm font-medium transition inline-flex items-center gap-2"
          :class="activeKey === p.key ? 'text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
          :style="activeKey === p.key && themeColor ? { backgroundColor: themeColor } : {}"
          @click="emit('navigate', p.key)"
        >
          <component :is="getIcon(p?.icon)" class="w-4 h-4" />
          <span>{{ p.title }}</span>
        </button>
      </div>

      <div class="flex items-center gap-1">
        <button class="px-3 py-2 rounded-full text-sm font-medium text-slate-700 hover:text-slate-900 hover:bg-slate-100 transition inline-flex items-center gap-2" @click="emit('open-customer')">
          <component :is="getIcon('User')" class="w-4 h-4" />
          <span>{{ isAuthenticated ? 'Minha conta' : 'Entrar' }}</span>
        </button>
        <button class="px-3 py-2 rounded-full text-sm font-medium text-slate-700 hover:text-slate-900 hover:bg-slate-100 transition inline-flex items-center gap-2" @click="emit('open-cart')">
          <component :is="getIcon('ShoppingCart')" class="w-4 h-4" />
          <span>Carrinho {{ Number(cartCount || 0) }}</span>
        </button>
        <a
          v-if="whatsappUrl"
          :href="whatsappUrl"
          target="_blank"
          class="px-3 py-2 rounded-full text-sm font-medium text-emerald-700 hover:text-emerald-800 hover:bg-emerald-50 transition inline-flex items-center gap-2"
        >
          <component :is="getIcon('MessageCircle')" class="w-4 h-4" />
          <span>WhatsApp</span>
        </a>
      </div>
    </div>
  </section>
</template>
