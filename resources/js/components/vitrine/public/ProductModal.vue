<script setup lang="ts">
import { ref, watch } from 'vue'
const props = defineProps({
    modelValue: Boolean,
    product: { type: Object, default: null },
    user: { type: Object, default: () => ({}) }
})
const emit = defineEmits(['update:modelValue'])

const idx = ref(0)
watch(() => props.product, () => idx.value = 0)

function close() { emit('update:modelValue', false) }

function whatsappHref() {
    const phone = (props.user?.phone_primary || '').replace(/\D/g, '')
    const message = `Olá! Gostaria de comprar o produto "${props.product?.name}".`
    if (!phone) return '#'
    return `https://wa.me/${phone}?text=${encodeURIComponent(message)}`
}
</script>

<template>
    <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-slate-900 rounded-xl w-full max-w-2xl overflow-hidden">
            <div class="flex-col gap-3 p-4">
                <div class="w-full">
                    <div class="h-64 bg-slate-100 rounded-lg overflow-hidden">
                        <img :src="product.images?.[idx]?.image_base64 || ('/' + product.images?.[idx]?.image_path)"
                            loading="lazy" class="w-full h-full object-cover" />
                    </div>
                    <div class="flex gap-2 mt-2 overflow-x-auto">
                        <img v-for="(img, i) in product.images" :key="i"
                            :src="img.image_base64 || ('/' + img.image_path)"
                            class="w-16 h-16 object-cover rounded-lg cursor-pointer" @click="idx = i" />
                    </div>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold">{{ product.name }}</h3>
                    <p class="text-slate-600 mt-2">{{ product.description }}</p>
                    <div class="mt-4">
                        <div class="text-sm text-slate-400 line-through" v-if="product.price">R$ {{
                            Number(product.price).toFixed(2) }}</div>
                        <div class="text-2xl font-extrabold text-emerald-600">R$ {{ Number(product.discount_price ||
                            product.price).toFixed(2) }}</div>
                    </div>

                    <a :href="whatsappHref()" target="_blank"
                        class="inline-block mt-6 bg-emerald-600 text-white px-4 py-3 rounded-lg font-semibold">Comprar
                        pelo WhatsApp</a>
                    <button class="ml-3 px-3 py-2 border rounded-lg" @click="close">Fechar</button>
                </div>
            </div>
        </div>
    </div>
</template>
