<!-- resources/js/components/vitrine/public/ProductModalFull.vue -->
<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import { getIcon } from '@/lib/iconMap'
import { formatCurrency } from '@/lib/utils'

const props = defineProps({
    show: { type: Boolean, default: false },
    product: { type: Object, default: null },
    user: { type: Object, default: () => ({}) }
})

const emit = defineEmits(['update:show'])

const visible = ref(props.show)
watch(() => props.show, v => visible.value = v)
watch(visible, v => emit('update:show', v))

const activeIndex = ref(0)
watch(() => props.product, () => activeIndex.value = 0)

const images = computed(() => props.product?.images ?? [])
const priceDisplay = computed(() => {
    if (!props.product) return ''
    return `R$ ${Number(props.product.discount_price || props.product.price || 0).toFixed(2)}`
})

function close() { visible.value = false }

// comprar via whatsapp: monta mensagem e abre wa.me
function buyNow() {
    const phone = (props.user?.phone_primary || '').replace(/\D/g, '')
    const text = encodeURIComponent(`Olá, tenho interesse no produto: ${props.product?.name} (id:${props.product?.id}). Preço: ${priceDisplay.value}`)
    const href = phone ? `https://wa.me/${phone}?text=${text}` : `https://wa.me/?text=${text}`
    window.open(href, '_blank')
}

// thumbnails select
function selectImage(i) { activeIndex.value = i }

// reviews infinite (simulação local)
const reviews = ref(props.product?.reviews ?? [])
const reviewsPage = ref(1)
const reviewsLoading = ref(false)
async function loadMoreReviews() {
    if (reviewsLoading.value) return
    reviewsLoading.value = true
    // se tiver API real, fetche aqui com page=reviewsPage
    setTimeout(() => {
        // simula append vazio (ou chamadas reais)
        reviewsPage.value++
        reviewsLoading.value = false
    }, 400)
}
</script>

<template>
    <transition name="modal-fade">
        <div v-if="visible" class="fixed inset-0 z-50 bg-black/40 flex items-end md:items-center justify-center">
            <div
                class="w-full md:max-w-md min-h-full bg-white md:rounded-2xl overflow-auto shadow-2xl">
                <!-- header -->
                <div class="flex items-center justify-between p-3 border-b">
                    <button @click="close" class="p-2 rounded-lg bg-white">
                        <component :is="getIcon('ChevronLeft')" class="w-5 h-5" />
                    </button>
                    <div class="text-sm font-semibold">{{ props.product?.name }}</div>
                    <div class="w-9"></div>
                </div>

                <!-- imagem grande -->
                <div class="p-4 flex items-center justify-center">
                    <div class="relative w-full h-56 md:h-72 flex items-center justify-center">
                        <div class="absolute -inset-6 rounded-full blur-3xl bg-sky-200 opacity-40"></div>
                        <img v-if="images.length"
                            :src="images[activeIndex]?.image_base64 || ('/' + images[activeIndex]?.image_path)"
                            class="max-h-full object-contain" loading="lazy" />
                        <div v-else class="h-40 w-full bg-slate-100 rounded-lg flex items-center justify-center">Sem
                            imagem</div>

                        <!-- dots -->
                        <div class="absolute bottom-2 left-1/2 -translate-x-1/2 flex gap-2">
                            <div v-for="(i, idx) in images" :key="idx"
                                :class="idx === activeIndex ? 'w-2 h-2 bg-white' : 'w-2 h-2 bg-white/40'"
                                class="rounded-full"></div>
                        </div>
                    </div>
                </div>

                <!-- thumbnails -->
                <div class="px-4 pb-3">
                    <div class="flex gap-2 overflow-x-auto">
                        <div v-for="(it, i) in images" :key="i" @click="selectImage(i)"
                            :class="['p-1 rounded-lg cursor-pointer', activeIndex === i ? 'border-2 border-sky-500' : 'border']">
                            <img :src="it.image_base64 || ('/' + it.image_path)"
                                class="w-20 h-14 object-cover rounded-md" loading="lazy" />
                        </div>
                    </div>
                </div>

                <!-- conteúdo -->
                <div class="p-4 space-y-3">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-bold text-lg">{{ props.product?.name }}</h3>
                            <p class="text-xs text-slate-500">{{ props.product?.brand ?? '' }}</p>
                        </div>
                        <div class="text-emerald-600 font-bold text-lg">{{ priceDisplay }}</div>
                    </div>

                    <p class="text-sm text-slate-600 line-clamp-3">{{ props.product?.description }}</p>

                    <!-- atributos: estoque, rating -->
                    <div class="flex items-center gap-3 text-xs text-slate-500">
                        <div>Estoque: <b class="text-slate-700">{{ props.product?.stock ?? 0 }}</b></div>
                        <div class="ml-2 flex items-center gap-1">
                            <component :is="getIcon('Star')" class="w-4 h-4 text-yellow-400" />
                            <span>{{ props.product?.rating ?? '—' }}</span>
                        </div>
                    </div>

                    <!-- ações -->
                    <div class="flex gap-3 mt-3">
                        <button @click="buyNow"
                            class="flex-1 bg-emerald-600 text-white py-3 rounded-2xl font-semibold">Comprar via
                            WhatsApp</button>
                        <button class="p-3 bg-white border rounded-2xl">
                            <component :is="getIcon('ShoppingCart')" class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- tabs descrição / avaliacoes -->
                    <div class="pt-2">
                        <div class="flex gap-2">
                            <button class="px-3 py-1 rounded-full bg-slate-100 text-sm">Descrição</button>
                            <button class="px-3 py-1 rounded-full bg-slate-100 text-sm">Avaliações ({{
                                (props.product?.reviews || []).length }})</button>
                        </div>

                        <div class="mt-3 space-y-2">
                            <div v-for="r in (props.product?.reviews || []).slice(0, 5)" :key="r.id"
                                class="p-3 bg-slate-50 rounded-xl">
                                <div class="flex items-center justify-between">
                                    <div class="font-medium text-sm">{{ r.customer_name }}</div>
                                    <div class="text-xs text-slate-400">{{ new
                                        Date(r.created_at).toLocaleDateString('pt-BR') }}</div>
                                </div>
                                <div class="text-sm text-slate-700 mt-1">{{ r.comment }}</div>
                            </div>

                            <div v-if="reviewsLoading" class="text-center text-sm text-slate-500">Carregando
                                avaliações...</div>
                            <button v-if="!reviewsLoading" @click="loadMoreReviews"
                                class="w-full text-sm text-sky-600">Carregar mais avaliações</button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </transition>
</template>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity .15s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
