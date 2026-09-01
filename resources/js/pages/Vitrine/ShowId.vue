<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import ProductPageFull from '@/components/vitrine/public/ProductPageFull.vue'
import DesktopStoreNav from '@/components/vitrine/public/DesktopStoreNav.vue'
import StoreCustomerPanel from '@/components/vitrine/public/StoreCustomerPanel.vue'
import StoreCartPanel from '@/components/vitrine/public/StoreCartPanel.vue'
import { loadPublicUser } from '@/composables/usePublicUser'
import { useStoreCustomer } from '@/composables/useStoreCustomer'
import { useGuestStoreCart } from '@/composables/useGuestStoreCart'
import LoadingPage from './LoadingPage.vue'

const path = window.location.pathname.split('/').filter(Boolean)
const slug = path[0]
const pageKey = path[1] ?? null
const itemId = Number(path[2]) ?? null

const user = ref<any>(null)
const pages = ref<any[]>([])
const currentPage = ref<any>(null)
const product = ref<any>(null)
const banners = ref<any[]>([])
const reviews = ref<any[]>([])
const loadingInitial = ref(true)
const customerPanelOpen = ref(false)
const cartPanelOpen = ref(false)
const message = ref<string | null>(null)

const customerApi = useStoreCustomer(slug)
const guestCart = useGuestStoreCart(slug)

const customer = computed(() => customerApi.customer.value)
const orders = computed(() => customerApi.orders.value)
const favorites = computed(() => customerApi.favorites.value)
const addresses = computed(() => customerApi.addresses.value)
const isAuthenticated = computed(() => customerApi.isAuthenticated.value)
const favoriteProductIds = computed(() => customerApi.favoriteProductIds.value)
const displayCart = computed(() => (isAuthenticated.value ? customerApi.cart.value : guestCart.cart.value))
const displayTotals = computed(() => (isAuthenticated.value ? customerApi.cartTotals.value : guestCart.totals.value))

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

async function loadPages() {
  const response = await axios.get(`/api/v1/users/${slug}/pages`)
  const list = Array.isArray(response.data) ? response.data : response.data?.data ?? []
  pages.value = list
  currentPage.value = list.find((p: any) => p.key === pageKey) ?? list[0] ?? null
}

async function loadProduct() {
  const response = await axios.get(`/api/v1/users/${slug}/products/${itemId}`)
  product.value = response.data?.data ?? response.data
}

async function loadBanners() {
  const response = await axios.get(`/api/v1/users/${slug}/banners`)
  banners.value = response.data?.data ?? response.data
}

async function loadReviews() {
  const response = await axios.get(`/api/v1/users/${slug}/products/${itemId}/reviews`)
  reviews.value = response.data?.data ?? response.data
}

function goBackToProducts() {
  if (!currentPage.value?.key) return
  router.get(route('vitrine.public.page', { slug, page: currentPage.value.key }))
}

async function syncGuestCartToServer() {
  const payload = guestCart.toSyncPayload()

  for (const item of payload) {
    await customerApi.addToCart(item.product_id, item.quantity)
  }

  guestCart.clear()
}

async function handleAddCart(prod: any) {
  try {
    if (isAuthenticated.value) {
      await customerApi.addToCart(prod.id, 1)
    } else {
      guestCart.addProduct(prod, 1)
    }

    cartPanelOpen.value = true
    message.value = `${prod.name} adicionado ao carrinho.`
  } catch (error: any) {
    message.value = error?.response?.data?.message ?? 'Falha ao adicionar ao carrinho.'
  }
}

async function handleToggleFavorite(prod: any) {
  if (!isAuthenticated.value) {
    customerPanelOpen.value = true
    message.value = 'Entre para usar favoritos.'
    return
  }

  try {
    await customerApi.toggleFavorite(prod.id)
  } catch (error: any) {
    message.value = error?.response?.data?.message ?? 'Falha ao atualizar favorito.'
  }
}

async function handleLogin(payload: { email: string; password: string }) {
  try {
    await customerApi.login(payload.email, payload.password)
    await syncGuestCartToServer()
    await Promise.all([customerApi.fetchCart(), customerApi.fetchFavorites(), customerApi.fetchOrders(), customerApi.fetchAddresses()])
    message.value = 'Login realizado com sucesso.'
  } catch (error: any) {
    message.value = error?.response?.data?.message ?? 'Falha ao realizar login.'
  }
}

async function handleRegister(payload: Record<string, any>) {
  try {
    await customerApi.register(payload)
    await syncGuestCartToServer()
    await Promise.all([customerApi.fetchCart(), customerApi.fetchFavorites(), customerApi.fetchOrders(), customerApi.fetchAddresses()])
    message.value = 'Cadastro realizado com sucesso.'
  } catch (error: any) {
    message.value = error?.response?.data?.message ?? 'Falha ao cadastrar cliente.'
  }
}

async function handleCheckout(payload: { addressId: number; paymentMethod: string; shippingMethod?: string; notes?: string }) {
  if (!isAuthenticated.value) {
    customerPanelOpen.value = true
    message.value = 'Para finalizar o pedido, entre ou crie uma conta.'
    return
  }

  try {
    await customerApi.checkout(payload.addressId, payload.paymentMethod, payload.shippingMethod, payload.notes)
    cartPanelOpen.value = false
    customerPanelOpen.value = true
    message.value = 'Pedido criado com sucesso.'
  } catch (error: any) {
    message.value = error?.response?.data?.message ?? 'Falha ao finalizar pedido.'
  }
}

async function handleOpenWhatsappOrder(orderId: number) {
  try {
    const link = await customerApi.fetchOrderWhatsappLink(orderId)
    if (link) window.open(link, '_blank')
  } catch (error: any) {
    message.value = error?.response?.data?.message ?? 'Falha ao gerar link de WhatsApp.'
  }
}

async function handleRepeatOrder(orderId: number) {
  try {
    await customerApi.repeatOrder(orderId)
    cartPanelOpen.value = true
    message.value = 'Itens do pedido adicionados ao carrinho.'
  } catch (error: any) {
    message.value = error?.response?.data?.message ?? 'Falha ao repetir pedido.'
  }
}

async function handleSaveAddress(payload: { address: Record<string, any>; id?: number }) {
  try {
    await customerApi.saveAddress(payload.address, payload.id)
    message.value = 'Endereco salvo com sucesso.'
  } catch (error: any) {
    message.value = error?.response?.data?.message ?? 'Falha ao salvar endereco.'
  }
}

async function handleDeleteAddress(addressId: number) {
  try {
    await customerApi.deleteAddress(addressId)
    message.value = 'Endereco removido.'
  } catch (error: any) {
    message.value = error?.response?.data?.message ?? 'Falha ao remover endereco.'
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
  await Promise.all([loadPages(), loadProduct(), loadBanners(), loadReviews(), customerApi.bootstrap()])

  loadingInitial.value = false
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
    <DesktopStoreNav
      :pages="pages"
      :active-key="currentPage?.key"
      :theme-color="user?.theme_color"
      :is-authenticated="isAuthenticated"
      :cart-count="Number(displayTotals?.items_count || 0)"
      :whatsapp-url="whatsappCartUrl"
      @navigate="(key) => router.get(route('vitrine.public.page', { slug, page: key }))"
      @open-customer="customerPanelOpen = true"
      @open-cart="cartPanelOpen = true"
    />

    <main class="container-custom md:max-w-none mx-auto md:px-10 md:pt-6 flex-grow">
      <div class="md:rounded-none md:border-0 md:bg-transparent md:backdrop-blur-none md:p-0 md:shadow-none">
        <div v-if="message" class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-900">{{ message }}</div>

        <div class="mt-4 flex justify-end gap-2 md:hidden">
          <button class="px-3 py-1.5 rounded-full border bg-white text-xs font-semibold" @click="customerPanelOpen = true">{{ customer ? 'Minha conta' : 'Entrar' }}</button>
          <button v-if="['store', 'hybrid'].includes(currentPage?.catalog_mode || 'store')" class="px-3 py-1.5 rounded-full border bg-white text-xs font-semibold" @click="cartPanelOpen = true">Carrinho {{ Number(displayTotals?.items_count || 0) }}</button>
        </div>

        <ProductPageFull
          :product="product"
          :banners="banners"
          :reviews="reviews"
          :user="user"
          :is-customer-authenticated="isAuthenticated"
          :favorite-product-ids="favoriteProductIds"
          :catalog-mode="currentPage?.catalog_mode || 'store'"
          @back="goBackToProducts"
          @add-cart="handleAddCart"
          @toggle-favorite="handleToggleFavorite"
          @open-customer-panel="customerPanelOpen = true"
        />
      </div>
    </main>

    <footer class="mt-6">
      <div class="container-custom md:max-w-none mx-auto px-4 md:px-10 py-6 text-sm text-slate-600 md:rounded-none md:border-0 md:bg-transparent md:backdrop-blur-none md:shadow-none">
        <h2 class="text-xl md:text-xl font-extrabold items-center flex mb-3">Sobre</h2>
        <p class="leading-relaxed">{{ user?.description }}</p>
      </div>
      <div class="container-custom md:max-w-none mx-auto px-4 md:px-10 py-6 text-sm text-slate-500 text-center">
        &copy; {{ new Date().getFullYear() }} {{ user?.business_name }}
        <div class="container-custom md:max-w-none mx-auto px-4 text-xs mt-2 text-slate-500 text-center">
          Desenvolvido por
          <a href="https://vitrinetop.hydradigital.com.br" target="_blank" class="font-extrabold">vitrine.top</a>
        </div>
      </div>
    </footer>

    <StoreCustomerPanel
      :open="customerPanelOpen"
      :customer="customer"
      :orders="orders"
      :favorites="favorites"
      :addresses="addresses"
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
      :addresses="addresses"
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
