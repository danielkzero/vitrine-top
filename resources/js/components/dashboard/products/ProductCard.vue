<template>
    <div class="flex cursor-pointer items-center gap-4 rounded-lg border border-border bg-card p-4 text-card-foreground shadow-sm transition hover:shadow-lg"
        @click="$emit('edit', product)">
        <div class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-lg border border-border bg-muted">
            <img v-if="firstImage" :src="firstImage" class="w-full h-full object-cover" :alt="product.name" />
            <component v-else :is="getIcon('Package')" class="h-7 w-7 text-muted-foreground" />
        </div>

        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between gap-4">
                <div class="truncate">
                    <p class="truncate font-medium text-foreground">{{ product.name }}</p>
                    <p class="truncate text-xs text-muted-foreground">{{ product.description }}</p>
                </div>

                <div class="text-right">
                    <p v-if="product.discount_price" class="text-xs text-muted-foreground line-through">{{
                        formatCurrency(product.price) }}</p>
                    <p class="font-bold text-emerald-600 dark:text-emerald-300">{{ formatCurrency(product.discount_price
                        || product.price) }}</p>
                    <p class="text-xs text-muted-foreground">Estoque: {{ product.stock ?? 0 }}</p>
                </div>
            </div>

            <div class="mt-2 flex items-center gap-2">
                <span v-if="product.featured"
                    class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">Destaque</span>
                <span v-if="!product.is_public"
                    class="rounded-full bg-muted px-2 py-0.5 text-xs text-muted-foreground">Privado</span>
                <span class="text-xs text-muted-foreground">{{ categoryName }}</span>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { getIcon } from '@/lib/iconMap'
import type { PropType } from 'vue'

const props = defineProps({
    product: { type: Object as PropType<any>, required: true },
    formatCurrency: { type: Function as PropType<(v: any) => string>, required: true },
    categorias: { type: Array as PropType<any[]>, default: () => [] }
})

defineEmits<{ (e: 'edit', p: any): void }>()

const firstImage = computed(() => {
    const imgs = props.product?.images ?? []
    if (!imgs || !imgs.length) return null
    // Accept objects with image_base64 or url or image_path
    return imgs[0].image_base64 || imgs[0].url || imgs[0].image_path || null
})

const categoryName = computed(() => {
    const id = props.product?.category_id
    if (!id) return ''
    const c = (props.categorias || []).find((x: any) => x.id === id)
    return c ? c.name : ''
})

const formatCurrency = props.formatCurrency
</script>

<style scoped>
/* nothing special; styling done with Tailwind classes */
</style>
