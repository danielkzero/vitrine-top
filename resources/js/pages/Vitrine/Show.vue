<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import SimplePublic from '@/components/vitrine/public/SimplePublic.vue'
import LinksPublic from '@/components/vitrine/public/LinksPublic.vue'
import GalleryPublic from '@/components/vitrine/public/GalleryPublic.vue'
import ReviewsPublic from '@/components/vitrine/public/ReviewsPublic.vue'
import ProductsPublic from '@/components/vitrine/public/ProductsPublic.vue'
import BottomNav from '@/components/vitrine/public/BottomNav.vue'
import DesktopStoreNav from '@/components/vitrine/public/DesktopStoreNav.vue'
import StoreCustomerPanel from '@/components/vitrine/public/StoreCustomerPanel.vue'
import StoreCartPanel from '@/components/vitrine/public/StoreCartPanel.vue'
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import axios from 'axios'
import { getIcon } from '@/lib/iconMap'
import { loadPublicUser } from '@/composables/usePublicUser'
import { useStoreCustomer } from '@/composables/useStoreCustomer'
import { useGuestStoreCart } from '@/composables/useGuestStoreCart'
import LoadingPage from './LoadingPage.vue'

const path = window.location.pathname.split('/').filter(Boolean)
const slug = path[0]
const pageKey = ref<string | null>(path[1] ?? null)

const user = ref<any>(null)
const pages = ref<any[]>([])
const currentPage = ref<any>(null)

const products = ref<any[]>([])
const categories = ref<any[]>([])
const reviews = ref<any[]>([])
const banners = ref<any[]>([])

const loadingInitial = ref(true)
const loadingContent = ref(false)
const dataCache = new Map<string, any>()

const customerPanelOpen = ref(false)
const cartPanelOpen = ref(false)
const customerApi = useStoreCustomer(slug)
const guestCart = useGuestStoreCart(slug)
const customerMessage = ref<string | null>(null)

const isAuthenticated = computed(() => customerApi.isAuthenticated.value)
const displayCart = computed(() => (isAuthenticated.value ? customerApi.cart.value : guestCart.cart.value))
const displayTotals = computed(() => (isAuthenticated.value ? customerApi.cartTotals.value : guestCart.totals.value))
const cartItemsCount = computed(() => Number(displayTotals.value?.items_count ?? 0))

const whatsappCartUrl = computed(() => {
  if (!user.value) return null

  const phone = String(user.value?.whatsapp || user.value?.phone_primary || '').replace(/\D+/g, '')
  if (!phone) return null

  const items = displayCart.value?.items ?? []
  if (!items.length) return null

  const lines = [
    `Ola, quero pedir na loja ${user.value?.business_name || ''}.`,
    '',
    'Itens do carrinho:',
  ]

  items.forEach((item: any) => {
    const name = item.product?.name ?? `Produto ${item.product_id}`
    lines.push(`- ${item.quantity}x ${name}`)
  })

  lines.push('', `Total: R$ ${Number(displayTotals.value?.total ?? 0).toFixed(2).replace('.', ',')}`)

  return `https://wa.me/${phone}?text=${encodeURIComponent(lines.join('\n'))}`
})

function resolvePageComponent(page: any) {
  const components: Record<string, any> = {
    simple: SimplePublic,
    links: LinksPublic,
    gallery: GalleryPublic,
    reviews: ReviewsPublic,
    products: ProductsPublic,
  }

  return components[page?.type] || SimplePublic
}

const pageLocal = computed(() => currentPage.value)
const heroImage = computed(() => user.value?.background_path ?? '')

async function loadPages() {
  const response = await axios.get(`/api/v1/users/${slug}/pages`)
  const list = Array.isArray(response.data) ? response.data : response.data?.data ?? []

  pages.value = list
  currentPage.value = list.find((p: any) => p.key === pageKey.value) ?? list[0] ?? null
}

async function trackPageView(currentKey?: string | null) {
  if (!currentKey) return

  try {
    await axios.get(`/api/store/${slug}/pages/${currentKey}`)
  } catch {
    // non-blocking tracking
  }
}

async function loadDependencies(page: any) {
  if (!page) return

  if (page.type === 'products') {
    if (!dataCache.has('products')) {
      const [bannersRes, productsRes, categoriesRes] = await Promise.all([
        axios.get(`/api/v1/users/${slug}/banners`),
        axios.get(`/api/v1/users/${slug}/products`),
        axios.get(`/api/v1/users/${slug}/categories`),
      ])

      dataCache.set('products', {
        banners: bannersRes.data?.data ?? [],
        products: productsRes.data?.data ?? [],
        categories: categoriesRes.data?.data ?? [],
      })
    }

    const payload = dataCache.get('products')
    banners.value = payload.banners
    products.value = payload.products
    categories.value = payload.categories
    reviews.value = []
    return
  }

  if (page.type === 'reviews') {
    if (!dataCache.has('reviews')) {
      const reviewsRes = await axios.get(`/api/v1/users/${slug}/reviews`)
      dataCache.set('reviews', reviewsRes.data?.data ?? [])
    }

    reviews.value = dataCache.get('reviews') ?? []
    products.value = []
    categories.value = []
    banners.value = []
    return
  }

  products.value = []
  categories.value = []
  banners.value = []
  reviews.value = []
}

async function changePage(nextPageKey: string, pushHistory = true) {
  if (!nextPageKey || pageKey.value === nextPageKey) return

  const nextPage = pages.value.find((p: any) => p.key === nextPageKey)
  if (!nextPage) return

  loadingContent.value = true
  pageKey.value = nextPage.key
  currentPage.value = nextPage

  if (pushHistory) {
    window.history.pushState({}, '', `/${slug}/${nextPage.key}`)
  }

  await loadDependencies(nextPage)
  await trackPageView(nextPage.key)
  loadingContent.value = false
}

function goToPage(page: any) {
  changePage(page.key, true)
}

function handlePopState() {
  const currentPath = window.location.pathname.split('/').filter(Boolean)
  const currentKey = currentPath[1] ?? null

  if (!currentKey) return
  changePage(currentKey, false)
}

async function syncGuestCartToServer() {
  const payload = guestCart.toSyncPayload()

  for (const item of payload) {
    await customerApi.addToCart(item.product_id, item.quantity)
  }

  guestCart.clear()
}

async function handleLogin(payload: { email: string; password: string }) {
  try {
    await customerApi.login(payload.email, payload.password)
    await syncGuestCartToServer()
    await Promise.all([customerApi.fetchCart(), customerApi.fetchFavorites(), customerApi.fetchOrders(), customerApi.fetchAddresses()])
    customerMessage.value = 'Login realizado com sucesso.'
  } catch (error: any) {
    customerMessage.value = error?.response?.data?.message ?? 'Falha ao realizar login.'
  }
}

async function handleRegister(payload: Record<string, any>) {
  try {
    await customerApi.register(payload)
    await syncGuestCartToServer()
    await Promise.all([customerApi.fetchCart(), customerApi.fetchFavorites(), customerApi.fetchOrders(), customerApi.fetchAddresses()])
    customerMessage.value = 'Cadastro realizado com sucesso.'
  } catch (error: any) {
    customerMessage.value = error?.response?.data?.message ?? 'Falha ao cadastrar cliente.'
  }
}

async function handleAddCart(product: any) {
  try {
    if (isAuthenticated.value) {
      await customerApi.addToCart(product.id, 1)
    } else {
      guestCart.addProduct(product, 1)
    }

    cartPanelOpen.value = true
    customerMessage.value = `${product.name} adicionado ao carrinho.`
  } catch (error: any) {
    customerMessage.value = error?.response?.data?.message ?? 'Falha ao adicionar ao carrinho.'
  }
}

async function handleToggleFavorite(product: any) {
  if (!isAuthenticated.value) {
    customerPanelOpen.value = true
    customerMessage.value = 'Entre para usar favoritos.'
    return
  }

  try {
    await customerApi.toggleFavorite(product.id)
  } catch (error: any) {
    customerMessage.value = error?.response?.data?.message ?? 'Falha ao atualizar favorito.'
  }
}

async function handleCheckout(payload: { addressId: number; paymentMethod: string; shippingMethod?: string; notes?: string }) {
  if (!isAuthenticated.value) {
    customerPanelOpen.value = true
    customerMessage.value = 'Para finalizar o pedido, entre ou crie uma conta.'
    return
  }

  try {
    await customerApi.checkout(payload.addressId, payload.paymentMethod, payload.shippingMethod, payload.notes)
    cartPanelOpen.value = false
    customerPanelOpen.value = true
    customerMessage.value = 'Pedido criado com sucesso.'
  } catch (error: any) {
    customerMessage.value = error?.response?.data?.message ?? 'Falha ao finalizar pedido.'
  }
}

async function handleOpenWhatsappOrder(orderId: number) {
  try {
    const link = await customerApi.fetchOrderWhatsappLink(orderId)
    if (link) window.open(link, '_blank')
  } catch (error: any) {
    customerMessage.value = error?.response?.data?.message ?? 'Falha ao gerar link de WhatsApp.'
  }
}

async function handleRepeatOrder(orderId: number) {
  try {
    await customerApi.repeatOrder(orderId)
    cartPanelOpen.value = true
    customerMessage.value = 'Itens do pedido adicionados ao carrinho.'
  } catch (error: any) {
    customerMessage.value = error?.response?.data?.message ?? 'Falha ao repetir pedido.'
  }
}

async function handleSaveAddress(payload: { address: Record<string, any>; id?: number }) {
  try {
    await customerApi.saveAddress(payload.address, payload.id)
    customerMessage.value = 'Endereço salvo com sucesso.'
  } catch (error: any) {
    customerMessage.value = error?.response?.data?.message ?? 'Falha ao salvar endereço.'
  }
}

async function handleDeleteAddress(addressId: number) {
  try {
    await customerApi.deleteAddress(addressId)
    customerMessage.value = 'Endereço removido.'
  } catch (error: any) {
    customerMessage.value = error?.response?.data?.message ?? 'Falha ao remover endereço.'
  }
}

async function handleUpdateCartItem(payload: { itemId: string | number; quantity: number }) {
  if (isAuthenticated.value) {
    await customerApi.updateCartItem(Number(payload.itemId), payload.quantity)
    return
  }

  guestCart.updateItem(payload.itemId, payload.quantity)
}

async function handleRemoveCartItem(itemId: string | number) {
  if (isAuthenticated.value) {
    await customerApi.removeCartItem(Number(itemId))
    return
  }

  guestCart.removeItem(itemId)
}

async function handleClearCart() {
  if (isAuthenticated.value) {
    await customerApi.clearCart()
    return
  }

  guestCart.clear()
}

onMounted(async () => {
  loadingInitial.value = true

  user.value = await loadPublicUser(slug)
  await loadPages()
  await loadDependencies(currentPage.value)
  await customerApi.bootstrap()
  await trackPageView(currentPage.value?.key ?? null)

  loadingInitial.value = false
  window.addEventListener('popstate', handlePopState)
})

onBeforeUnmount(() => {
  window.removeEventListener('popstate', handlePopState)
})
</script>

<template>
  <Head :title="user?.business_name">
    <link rel="icon" type="image/png" :href="user?.logo_path || ''" />
  </Head>

  <LoadingPage :loading="loadingInitial" />

  <div
    v-if="!loadingInitial"
    class="min-h-screen flex flex-col bg-[radial-gradient(circle_at_top,_#f8fafc,_#eef2ff_45%,_#f8fafc)] text-slate-900 md:bg-[#f5f5f7]"
  >
    <header
      class="container-custom mx-auto relative w-full h-[10rem] md:h-[20rem] flex items-center justify-center bg-cover bg-center bg-no-repeat rounded-b-3xl md:rounded-none shadow-xl md:shadow-none md:mt-0 md:overflow-hidden"
      :style="{ backgroundImage: `url('${heroImage}')` }"
    >
      <div class="absolute inset-0 bg-gradient-to-t from-black/65 to-black/35 backdrop-blur-[1px] rounded-b-3xl md:rounded-none"></div>

      <div class="relative z-10 text-center text-white px-4 md:px-8">
        <h1 class="text-4xl md:text-5xl font-extrabold drop-shadow-lg items-center flex justify-center">
          <img v-if="user.logo_path" :src="user.logo_path" class="w-14 mr-3 rounded-2xl bg-white/90 shadow md:w-16" />
          {{ user?.business_name }}
        </h1>
        <p class="mt-3 text-lg opacity-90 md:text-xl">{{ user?.subtitle ?? '' }}</p>
      </div>

      <div class="absolute right-4 top-4 z-20 flex gap-2 md:hidden">
        <button class="px-3 py-1.5 rounded-full bg-white/95 text-slate-800 text-xs font-semibold shadow" @click="customerPanelOpen = true">{{ isAuthenticated ? 'Minha conta' : 'Entrar' }}</button>
        <button class="px-3 py-1.5 rounded-full bg-white/95 text-slate-800 text-xs font-semibold shadow" @click="cartPanelOpen = true">Carrinho {{ cartItemsCount }}</button>
      </div>
    </header>

    <DesktopStoreNav
      :pages="pages"
      :active-key="pageLocal?.key"
      :theme-color="user?.theme_color"
      :is-authenticated="isAuthenticated"
      :cart-count="cartItemsCount"
      :whatsapp-url="whatsappCartUrl"
      @navigate="(key) => changePage(key, true)"
      @open-customer="customerPanelOpen = true"
      @open-cart="cartPanelOpen = true"
    />

    <main class="container-custom mx-auto px-3 md:px-10 md:pt-6 flex-grow">
      <section>
        <div class="md:rounded-none md:border-0 md:bg-transparent md:backdrop-blur-none md:p-0 md:shadow-none">
          <h2 class="text-xl font-bold items-center flex py-6 md:hidden">
            <component :is="getIcon(pageLocal?.icon)" class="mr-2" />
            {{ pageLocal?.title }}
          </h2>


          <div v-if="customerMessage" class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-900">
            {{ customerMessage }}
          </div>

          <div v-if="loadingContent" class="space-y-3 animate-pulse">
            <div class="h-6 rounded bg-slate-200 w-1/3"></div>
            <div class="h-24 rounded bg-slate-200"></div>
            <div class="h-24 rounded bg-slate-200"></div>
            <div class="h-24 rounded bg-slate-200"></div>
          </div>

          <component
            v-else-if="pageLocal"
            :is="resolvePageComponent(pageLocal)"
            :user="user"
            :page="pageLocal"
            :products="products"
            :categories="categories"
            :reviews="reviews"
            :banners="banners"
            :cart-count="cartItemsCount"
            :favorite-product-ids="customerApi.favoriteProductIds.value"
            :is-customer-authenticated="isAuthenticated"
            @open-customer-panel="customerPanelOpen = true"
            @open-cart="cartPanelOpen = true"
            @add-cart="handleAddCart"
            @toggle-favorite="handleToggleFavorite"
          />
        </div>
      </section>
    </main>

    <div class="md:hidden">
      <BottomNav :user="user" :pages="pages" :activeKey="pageLocal?.key" @navigate="goToPage" />
    </div>

    <footer class="pb-24 md:pb-8 mt-6">
      <div class="container-custom mx-auto px-4 md:px-10 py-6 text-sm text-slate-600 md:rounded-none md:border-0 md:bg-transparent md:backdrop-blur-none md:shadow-none">
        <h2 class="text-xl md:text-xl font-extrabold items-center flex mb-3">Sobre</h2>
        <p class="leading-relaxed">{{ user?.description }}</p>
      </div>
      <div class="container-custom mx-auto px-4 md:px-10 py-6 text-sm text-slate-500 text-center">
        &copy; {{ new Date().getFullYear() }} {{ user?.business_name }}
        <div class="container-custom mx-auto px-4 text-xs mt-2 text-slate-500 text-center">
          Desenvolvido por
          <a href="https://vitrinetop.hydradigital.com.br" target="_blank" class="font-extrabold">vitrine.top</a>
        </div>
      </div>
    </footer>

    <StoreCustomerPanel
      :open="customerPanelOpen"
      :customer="customerApi.customer.value"
      :orders="customerApi.orders.value"
      :favorites="customerApi.favorites.value"
      :addresses="customerApi.addresses.value"
      :loading="customerApi.loading.value"
      :store-theme-color="user?.theme_color"
      @close="customerPanelOpen = false"
      @login="handleLogin"
      @register="handleRegister"
      @logout="customerApi.logout"
      @save-address="handleSaveAddress"
      @delete-address="handleDeleteAddress"
      @repeat-order="handleRepeatOrder"
      @open-whatsapp-order="handleOpenWhatsappOrder"
    />

    <StoreCartPanel
      :open="cartPanelOpen"
      :cart="displayCart"
      :totals="displayTotals"
      :addresses="customerApi.addresses.value"
      :is-authenticated="isAuthenticated"
      :whatsapp-cart-url="whatsappCartUrl"
      :theme-color="user?.theme_color"
      @close="cartPanelOpen = false"
      @clear="handleClearCart"
      @remove-item="handleRemoveCartItem"
      @update-item="handleUpdateCartItem"
      @checkout="handleCheckout"
      @open-customer-panel="customerPanelOpen = true"
    />
  </div>
</template>
