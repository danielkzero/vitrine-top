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
    reviews: { type: Array, default: () => [] }
})

const emit = defineEmits(['back'])

/* ==========================================
   PRODUTO ATUAL (agora é único)
========================================== */
const product = computed(() => props.product)

/* ==========================================
   STATE
========================================== */
const activeIndex = ref(0)

watch(product, () => {
    activeIndex.value = 0
})

const images = computed(() => product.value?.images ?? [])

/* ==========================================
   WHATSAPP
========================================== */
function buyNow() {
    const phone = (props.user?.phone_primary || '').replace(/\D/g, '') || ''
    const text = encodeURIComponent(
        `Olá, tenho interesse no produto: ${product.value?.name} (ID: ${product.value?.id}). Preço: ${
            product.value.discount_price
                ? formatCurrency(product.value.discount_price)
                : formatCurrency(product.value.price)
        }`
    )

    const href = phone
        ? `https://wa.me/${phone}?text=${text}`
        : `https://wa.me/?text=${text}`

    window.open(href, '_blank')
}

/* ==========================================
   REVIEWS
========================================== */
const reviews = computed(() => props.reviews ?? [])
const reviewsPage = ref(1)
const reviewsLoading = ref(false)

function loadMoreReviews() {
    if (reviewsLoading.value) return

    reviewsLoading.value = true
    setTimeout(() => {
        reviewsPage.value++
        reviewsLoading.value = false
    }, 400)
}

/* ==========================================
   VOLTAR
========================================== */
function goBack() {
    emit('back')
}

/* ==========================================
   FORM DE AVALIAÇÃO
========================================== */
const showForm = ref(false)

const form = ref({
    customer_name: '',
    product_id: product.value?.id,
    whatsapp: '',
    rating: 5,
    comment: ''
})

function toggleForm() {
    showForm.value = !showForm.value
}

function submitReview() {
    form.value.product_id = product.value.id

    router.post(
        route('vitrine.reviews.store', {
            slug: props.user.slug,
            product: product.value.id
        }),
        form.value,
        {
            onSuccess: () => {
                form.value = {
                    customer_name: '',
                    whatsapp: '',
                    rating: 5,
                    comment: ''
                }
                alert('Avaliação enviada com sucesso!')
            }
        }
    )
}
</script>

<template>
    <div class="container-custom w-full mx-auto min-h-screen bg-white md:rounded-2xl shadow">

        <!-- HEADER -->
        <div class="flex items-center justify-between p-3 border-b">
            <button @click="goBack" class="p-2 bg-white rounded-xl border shadow-sm">
                <component :is="getIcon('ChevronLeft')" class="w-5 h-5" />
            </button>

            <div class="text-sm font-semibold">Detalhes do Produto</div>

            <button class="p-2 bg-white rounded-xl border shadow-sm">
                <component :is="getIcon('Heart')" class="w-5 h-5" />
            </button>
        </div>

        <!-- IMAGEM PRINCIPAL -->
        <div class="p-4 flex items-center justify-center">
            <div class="relative w-full h-60 flex items-center justify-center">
                <img
                    v-if="images.length"
                    :src="images[activeIndex]?.image || '/' + images[activeIndex]?.image_path"
                    class="max-h-full object-contain"
                />

                <div v-else class="h-40 w-full bg-slate-100 flex items-center justify-center rounded-lg">
                    Sem imagem
                </div>

                <!-- dots -->
                <div class="absolute bottom-2 left-1/2 -translate-x-1/2 flex gap-2">
                    <div
                        v-for="(img, idx) in images"
                        :key="idx"
                        @click="activeIndex = idx"
                        class="w-3 h-3 rounded-full cursor-pointer"
                        :style="{ backgroundColor: props.user.theme_color, opacity: activeIndex === idx ? 1 : 0.3 }"
                    />
                </div>
            </div>
        </div>

        <!-- MINIATURAS -->
        <div class="px-4 pb-3">
            <div class="flex gap-2 overflow-x-auto">
                <div
                    v-for="(img, i) in images"
                    :key="i"
                    @click="activeIndex = i"
                    class="p-1 rounded-lg cursor-pointer border"
                    :style="{ borderColor: activeIndex === i ? props.user.theme_color : '#ccc' }"
                >
                    <img
                        :src="img.image || '/' + img.image_path"
                        class="w-20 h-14 object-cover rounded-md"
                    />
                </div>
            </div>
        </div>

        <!-- INFO DO PRODUTO -->
        <div class="p-4 space-y-3">

            <!-- Título + Preço -->
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="font-bold text-lg">{{ product?.name }}</h3>
                </div>

                <div>
                    <div v-if="product?.discount_price" class="line-through text-xs text-slate-400">
                        {{ formatCurrency(product?.price) }}
                    </div>

                    <div class="font-bold text-lg" :style="{ color: props.user.theme_color }">
                        {{ formatCurrency(product?.discount_price ?? product?.price) }}
                    </div>
                </div>
            </div>

            <!-- Descrição -->
            <p class="text-sm text-slate-600 whitespace-pre-line">{{ product?.description }}</p>

            <!-- Estoque + Nota -->
            <div class="flex items-center gap-3 text-xs text-slate-500">
                <div>
                    Estoque:
                    <b>{{ product?.stock ?? 0 }}</b>
                </div>

                <div class="flex items-center gap-1">
                    <component :is="getIcon('Star')" class="w-4 h-4 text-yellow-400" />
                    <span>{{ product?.rating ?? '—' }}</span>
                </div>
            </div>

            <!-- AÇÕES -->
            <div class="flex gap-3 mt-3">
                <button
                    @click="buyNow"
                    class="flex-1 text-white py-3 rounded-2xl font-semibold"
                    :style="{ backgroundColor: props.user.theme_color }"
                >
                    <div class="flex justify-center items-center">
                        <component :is="getIcon('ShoppingBag')" class="w-5 h-5 mr-2" />
                        Comprar via WhatsApp
                    </div>
                </button>

                <button class="p-3 bg-white border rounded-2xl text-slate-600">
                    <component :is="getIcon('ShoppingCart')" class="w-5 h-5" />
                </button>
            </div>

            <!-- AVALIAÇÕES -->
            <div class="pt-4">

                <div class="flex gap-2">
                    <button class="px-3 py-1 rounded-full bg-slate-100 text-sm">
                        Avaliações ({{ reviews.length }})
                    </button>

                    <button
                        @click="toggleForm"
                        class="bg-slate-600 text-white py-2 px-4 rounded-full font-semibold flex items-center"
                    >
                        <component :is="getIcon('UserStar')" class="w-5 h-5 mr-2" />
                        {{ showForm ? 'Fechar' : 'Avaliar' }}
                    </button>
                </div>

                <!-- FORMULÁRIO -->
                <transition name="fade">
                    <div
                        v-if="showForm"
                        class="mt-4 p-4 bg-slate-50 rounded-xl border"
                    >
                        <form @submit.prevent="submitReview">

                            <input
                                v-model="form.customer_name"
                                type="text"
                                placeholder="Seu nome"
                                class="w-full border rounded-xl p-3 mb-2"
                            />

                            <input
                                v-model="form.whatsapp"
                                type="text"
                                placeholder="Seu WhatsApp"
                                class="w-full border rounded-xl p-3 mb-2"
                            />

                            <div class="flex gap-1">
                                <component
                                    v-for="i in 5"
                                    :key="i"
                                    :is="getIcon(i <= form.rating ? 'Star' : 'StarOff')"
                                    @click="form.rating = i"
                                    class="w-6 h-6 cursor-pointer"
                                />
                            </div>

                            <textarea
                                v-model="form.comment"
                                rows="4"
                                placeholder="Comentário"
                                class="w-full border rounded-xl p-3 mt-3"
                            ></textarea>

                            <button
                                type="submit"
                                class="w-full py-3 rounded-xl text-white font-semibold mt-3"
                                :style="{ backgroundColor: props.user.theme_color }"
                            >
                                Enviar Avaliação
                            </button>

                        </form>
                    </div>
                </transition>

                <!-- LISTA DE REVIEWS -->
                <div class="mt-3 space-y-3">
                    <div
                        v-for="r in reviews.slice(0, 5)"
                        :key="r.id"
                        class="p-3 bg-slate-50 rounded-xl"
                    >
                        <div class="flex justify-between">
                            <b>{{ r.customer_name }}</b>
                            <small class="text-slate-400">
                                {{ new Date(r.created_at).toLocaleDateString('pt-BR') }}
                            </small>
                        </div>

                        <div class="flex gap-1 mt-1">
                            <component
                                v-for="n in 5"
                                :key="n"
                                :is="getIcon(n <= r.rating ? 'Star' : 'StarOff')"
                                class="w-4 h-4"
                                :class="n <= r.rating ? 'text-yellow-400' : 'text-slate-400'"
                            />
                        </div>

                        <p class="text-sm text-slate-700 mt-1">{{ r.comment }}</p>
                    </div>

                    <button
                        @click="loadMoreReviews"
                        class="w-full text-sm mt-2"
                        :style="{ color: props.user.theme_color }"
                    >
                        Carregar mais avaliações
                    </button>
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
