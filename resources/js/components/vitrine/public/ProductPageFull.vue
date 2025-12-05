<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import { getIcon } from '@/lib/iconMap'
import { formatCurrency } from '@/lib/utils'
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const props = defineProps({
    products: Array,
    itemId: Number,
    user: { type: Object, default: () => ({}) }
})

const emit = defineEmits(['back'])

/* ==========================================
   PRODUTO ATUAL
========================================== */
const product = computed(() =>
    props.products.find(p => p.id === props.itemId)
)

/* ==========================================
   ESTADOS (idênticos ao modal)
========================================== */
const activeIndex = ref(0)

watch(product, () => activeIndex.value = 0)

const images = computed(() => product.value?.images ?? [])

/* ==========================================
   WHATSAPP
========================================== */
function buyNow() {
    const phone = (props.user?.phone_primary || '').replace(/\D/g, '') || '+5524999699849'
    const text = encodeURIComponent(
        `Olá, tenho interesse no produto: ${product.value?.name} (id:${product.value?.id}). Preço: ${product.value.discount_price
            ? formatCurrency(product.value.discount_price)
            : formatCurrency(product.value.price)
        }`
    )
    const href = phone ? `https://wa.me/${phone}?text=${text}` : `https://wa.me/?text=${text}`
    window.open(href, '_blank')
}

/* ==========================================
   REVIEWS (mantidas para o futuro)
========================================== */
const reviews = ref(product.value?.reviews ?? [])
const reviewsPage = ref(1)
const reviewsLoading = ref(false)

async function loadMoreReviews() {
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


const form = ref({
    customer_name: '',
    product_id: product.value.id,
    whatsapp: '',
    rating: 5,
    comment: ''
})

const showForm = ref(false)

function toggleForm() {
    showForm.value = !showForm.value
}

function submitReview() {
    form.value.product_id = product.value.id;
    router.post(
        route('vitrine.reviews.store', {
            slug: props.user.slug,
            product: product.value.id
        }),
        form.value,
        {
            onSuccess: () => {
                form.value = { customer_name: '', whatsapp: '', rating: 5, comment: '' }
                alert('Avaliação enviada com sucesso!')
            }
        }
    )
}
</script>

<template>
    <div class="container-custom w-full mx-auto min-h-screen bg-white md:rounded-2xl shadow">

        <!-- HEADER (igual ao modal mas sem close duplicado) -->
        <div class="flex items-center justify-between p-3 border-b">
            <button @click="goBack" class="p-2 bg-white rounded-xl shadow-sm border border-slate-100">
                <component :is="getIcon('ChevronLeft')" class="w-5 h-5" />
            </button>

            <div class="text-sm font-semibold">
                Detalhes do Produto
            </div>

            <button class="p-2 bg-white rounded-xl shadow-sm border border-slate-100">
                <component :is="getIcon('Heart')" class="w-5 h-5" />
            </button>
        </div>

        <!-- IMAGEM GRANDE -->
        <div class="p-4 flex items-center justify-center">
            <div class="relative w-full h-56 md:h-72 flex items-center justify-center">
                <div class="absolute -inset-6 rounded-full blur-3xl bg-sky-200 opacity-20"></div>

                <img v-if="images.length"
                    :src="images[activeIndex]?.image_base64 || ('/' + images[activeIndex]?.image_path)"
                    class="max-h-full object-contain" loading="lazy" />

                <div v-else class="h-40 w-full bg-slate-100 rounded-lg flex items-center justify-center">
                    Sem imagem
                </div>

                <!-- dots -->
                <div class="absolute bottom-2 left-1/2 -translate-x-1/2 flex gap-2">
                    <div v-for="(i, idx) in images" :key="idx" 
                        @click="activeIndex = idx" 
                        :class="idx === activeIndex ? `w-3 h-3 shadow-2xl` :`w-3 h-3 shadow-2xl`"
                        :style="{ backgroundColor: props.user.theme_color, opacity: .5, border: props.user.theme_color }" 
                        class="rounded-full cursor-pointer"></div> 
                </div>
            </div>
        </div>

        <!-- THUMBNAILS -->
        <div class="px-4 pb-3">
            <div class="flex gap-2 overflow-x-auto">
                <div v-for="(it, i) in images" :key="i" @click="activeIndex = i" :class="[
                    'p-1 rounded-lg cursor-pointer',
                    activeIndex === i ? 'border-2' : 'border'
                ]" :style="{ borderColor: props.user.theme_color }"> 
                    <img :src="it.image_base64 || ('/' + it.image_path)" class="w-20 h-14 object-cover rounded-md"
                        loading="lazy" />
                </div>
            </div>
        </div>

        <!-- CONTEÚDO -->
        <div class="p-4 space-y-3">
            <!-- Título e Preço -->
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="font-bold text-lg">{{ product?.name }}</h3>
                    <p class="text-xs text-slate-500">{{ product?.brand ?? '' }}</p>
                </div>

                <div>
                    <div class="text-slate-400 line-through text-xs" v-if="product?.discount_price">
                        {{ formatCurrency(product?.price) }}
                    </div>
                    <div class="font-bold text-lg" :style="{ color: props.user.theme_color }">
                        {{ formatCurrency(product?.discount_price || product?.price) }}
                    </div>
                </div>
            </div>

            <!-- Descrição -->
            <p class="text-sm text-slate-600 line-clamp-3">
                {{ product?.description }}
            </p>

            <!-- Atributos -->
            <div class="flex items-center gap-3 text-xs text-slate-500">
                <div>
                    Estoque:
                    <b class="text-slate-700">
                        {{ product?.stock ?? 0 }}
                    </b>
                </div>

                <div class="ml-2 flex items-center gap-1">
                    <component :is="getIcon('Star')" class="w-4 h-4 text-yellow-400" />
                    <span>{{ product?.rating ?? '—' }}</span>
                </div>
            </div>

            <!-- Ações -->
            <div class="flex gap-3 mt-3">
                <button @click="buyNow" class="flex-1 text-white py-3 rounded-2xl font-semibold" :style="{ backgroundColor: props.user.theme_color }">
                    <div class="items-center flex justify-center">
                        <component :is="getIcon('ShoppingBag')" class="w-5 h-5 mr-2" />
                        Comprar via WhatsApp
                    </div>
                </button>

                <button class="p-3 bg-white border rounded-2xl text-slate-600">
                    <component :is="getIcon('ShoppingCart')" fill="currentColor" class="w-5 h-5" />
                </button>
            </div>

            <!-- Tabs -->
            <div class="pt-2">
                <div class="flex gap-2">
                    <!--<button class="px-3 py-1 rounded-full bg-slate-100 text-sm">Descrição</button>-->
                    <button class="px-3 py-1 rounded-full bg-slate-100 text-sm">
                        Avaliações ({{ (product?.reviews || []).length }})
                    </button>
                    <button @click="toggleForm"
                        class="bg-slate-600 text-white py-2 rounded-full font-semibold shadow-sm px-4 items-center flex">
                        <component :is="getIcon('UserStar')" class="mr-2 w-5 h-5" />
                        {{ showForm ? 'Fechar formulário' : 'Avaliar produto' }}
                    </button>
                </div>
                <!-- FORMULÁRIO DE AVALIAÇÃO -->
                <transition enter-active-class="transition-all duration-300 ease-out"
                    enter-from-class="opacity-0 -translate-y-3" enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition-all duration-200 ease-in"
                    leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-3">
                    <div v-if="showForm"
                        class="mt-6 p-4 bg-slate-50 rounded-xl shadow-sm space-y-3 border border-slate-200">

                        <h3 class="font-semibold text-slate-700 text-lg">
                            Enviar Avaliação
                        </h3>

                        <form @submit.prevent="submitReview">

                            <!-- Nome -->
                            <input v-model="form.customer_name" type="text" placeholder="Seu nome"
                                class="w-full border rounded-xl p-3 focus:ring-2 focus:ring-sky-300 outline-none"
                                required />

                            <!-- WhatsApp -->
                            <input v-model="form.whatsapp" type="text" placeholder="Seu WhatsApp (opcional)"
                                class="w-full border rounded-xl p-3 focus:ring-2 focus:ring-sky-300 outline-none" />
                            
                            <!-- Rating -->
                            <div class="flex gap-1 mt-2">
                                <component v-for="i in 5" :key="i" :is="getIcon(i <= form.rating ? 'Star' : 'StarOff')"
                                    @click="form.rating = i" class="w-7 h-7 cursor-pointer transition"
                                    :class="i <= form.rating ? 'text-yellow-400' : 'text-slate-400'" />
                            </div>

                            <!-- Comentário -->
                            <textarea v-model="form.comment" rows="4" placeholder="Escreva sua avaliação..."
                                class="w-full border rounded-xl p-3 mt-3 focus:ring-2 focus:ring-sky-300 outline-none"
                                required></textarea>

                            <!-- Enviar -->
                            <button type="submit"
                                class="w-full text-white py-3 rounded-xl font-semibold mt-3 shadow-sm" :style="{ backgroundColor: props.user.theme_color }">
                                Enviar Avaliação
                            </button>

                        </form>

                    </div>
                </transition>

                <!-- Lista de reviews -->
                <div class="mt-3 space-y-2">
                    <div v-for="r in (product?.reviews || []).slice(0, 5)" :key="r.id"
                        class="p-3 bg-slate-50 rounded-xl">
                        <div class="flex items-center justify-between">
                            <div class="font-medium text-sm">{{ r.customer_name }}</div>
                            <div class="text-xs text-slate-400">
                                {{ new Date(r.created_at).toLocaleDateString('pt-BR') }}
                            </div>
                        </div>

                        <div class="flex gap-1 mt-2">
                            <component v-for="n in 5" :key="n" :is="getIcon(n <= r.rating ? 'Star' : 'StarOff')"
                                class="w-4 h-4 " :class="n <= r.rating ? 'text-yellow-400' : 'text-slate-400'" />
                        </div>

                        <div class="text-sm text-slate-700 mt-1">
                            {{ r.comment }}
                        </div>
                    </div>

                    <div v-if="reviewsLoading" class="text-center text-sm text-slate-500">
                        Carregando avaliações...
                    </div>

                    <button v-if="!reviewsLoading" @click="loadMoreReviews" class="w-full text-sm" :style="{ color: props.user.theme_color }">
                        - Carregar mais avaliações -
                    </button>
                </div>




            </div>

        </div>
    </div>
</template>

<style scoped>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
