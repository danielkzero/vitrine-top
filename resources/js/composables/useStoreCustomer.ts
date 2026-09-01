import axios from 'axios'
import { computed, ref } from 'vue'

function tokenKey(storeSlug: string): string {
  return `store_customer_token:${storeSlug}`
}

export function useStoreCustomer(storeSlug: string) {
  const customer = ref<any | null>(null)
  const token = ref<string | null>(localStorage.getItem(tokenKey(storeSlug)))
  const cart = ref<any | null>(null)
  const cartTotals = ref<any>({ subtotal: 0, total: 0, items_count: 0 })
  const favorites = ref<any[]>([])
  const orders = ref<any[]>([])
  const addresses = ref<any[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  const isAuthenticated = computed(() => !!token.value && !!customer.value)
  const favoriteProductIds = computed<number[]>(() => favorites.value.map((item) => Number(item.product_id)).filter(Boolean))
  const cartItemsCount = computed<number>(() => Number(cartTotals.value?.items_count ?? 0))

  function setToken(newToken: string | null) {
    token.value = newToken
    if (newToken) {
      localStorage.setItem(tokenKey(storeSlug), newToken)
      return
    }
    localStorage.removeItem(tokenKey(storeSlug))
  }

  function authHeaders() {
    return token.value ? { Authorization: `Bearer ${token.value}` } : {}
  }

  async function bootstrap() {
    if (!token.value) {
      customer.value = null
      return
    }

    loading.value = true
    error.value = null

    try {
      await fetchMe()
      await Promise.all([fetchCart(), fetchFavorites(), fetchOrders(), fetchAddresses()])
    } catch (err: any) {
      setToken(null)
      customer.value = null
      error.value = err?.response?.data?.message ?? 'Falha ao autenticar cliente.'
    } finally {
      loading.value = false
    }
  }

  async function register(payload: Record<string, any>) {
    const response = await axios.post(`/api/store/${storeSlug}/customers/register`, payload)
    setToken(response.data?.token ?? null)
    customer.value = response.data?.customer ?? null
    await Promise.all([fetchCart(), fetchFavorites(), fetchOrders(), fetchAddresses()])
    return response.data
  }

  async function login(email: string, password: string) {
    const response = await axios.post(`/api/store/${storeSlug}/customers/login`, { email, password })
    setToken(response.data?.token ?? null)
    customer.value = response.data?.customer ?? null
    await Promise.all([fetchCart(), fetchFavorites(), fetchOrders(), fetchAddresses()])
    return response.data
  }

  async function logout() {
    if (token.value) {
      await axios.post(`/api/store/${storeSlug}/customers/logout`, {}, { headers: authHeaders() })
    }

    setToken(null)
    customer.value = null
    cart.value = null
    cartTotals.value = { subtotal: 0, total: 0, items_count: 0 }
    favorites.value = []
    orders.value = []
    addresses.value = []
  }

  async function fetchMe() {
    const response = await axios.get(`/api/store/${storeSlug}/customers/me`, { headers: authHeaders() })
    customer.value = response.data?.customer ?? null
    return customer.value
  }

  async function lookupZip(zip: string) {
    const response = await axios.get(`/api/store/${storeSlug}/zipcode`, { params: { zip } })
    return response.data
  }

  async function fetchCart() {
    const response = await axios.get(`/api/customer/${storeSlug}/cart`, { headers: authHeaders() })
    cart.value = response.data?.cart ?? null
    cartTotals.value = response.data?.totals ?? { subtotal: 0, total: 0, items_count: 0 }
    return cart.value
  }

  async function addToCart(productId: number, quantity = 1) {
    const response = await axios.post(`/api/customer/${storeSlug}/cart/items`, { product_id: productId, quantity }, { headers: authHeaders() })
    cart.value = response.data?.cart ?? null
    cartTotals.value = response.data?.totals ?? cartTotals.value
    return cart.value
  }

  async function updateCartItem(itemId: number, quantity: number) {
    const response = await axios.put(`/api/customer/${storeSlug}/cart/items/${itemId}`, { quantity }, { headers: authHeaders() })
    cart.value = response.data?.cart ?? null
    cartTotals.value = response.data?.totals ?? cartTotals.value
    return cart.value
  }

  async function removeCartItem(itemId: number) {
    const response = await axios.delete(`/api/customer/${storeSlug}/cart/items/${itemId}`, { headers: authHeaders() })
    cart.value = response.data?.cart ?? null
    cartTotals.value = response.data?.totals ?? cartTotals.value
    return cart.value
  }

  async function clearCart() {
    const response = await axios.delete(`/api/customer/${storeSlug}/cart`, { headers: authHeaders() })
    cart.value = response.data?.cart ?? null
    cartTotals.value = response.data?.totals ?? cartTotals.value
    return cart.value
  }

  async function checkout(addressId: number, paymentMethod: string, shippingMethod?: string, notes?: string) {
    const response = await axios.post(
      `/api/customer/${storeSlug}/orders/checkout`,
      {
        address_id: addressId,
        payment_method: paymentMethod,
        shipping_method: shippingMethod ?? null,
        notes: notes ?? null,
      },
      { headers: authHeaders() },
    )

    await Promise.all([fetchOrders(), fetchCart()])

    return response.data?.data
  }

  async function fetchOrders() {
    const response = await axios.get(`/api/customer/${storeSlug}/orders`, { headers: authHeaders() })
    orders.value = response.data?.data ?? []
    return orders.value
  }

  async function repeatOrder(orderId: number) {
    await axios.post(`/api/customer/${storeSlug}/orders/${orderId}/repeat`, {}, { headers: authHeaders() })
    await fetchCart()
  }

  async function fetchOrderWhatsappLink(orderId: number) {
    const response = await axios.get(`/api/customer/${storeSlug}/orders/${orderId}/whatsapp-link`, { headers: authHeaders() })
    return response.data?.whatsapp_url ?? null
  }

  async function fetchFavorites() {
    const response = await axios.get(`/api/customer/${storeSlug}/favorites`, { headers: authHeaders() })
    favorites.value = response.data?.data ?? []
    return favorites.value
  }

  async function toggleFavorite(productId: number) {
    const exists = favoriteProductIds.value.includes(productId)

    if (exists) {
      await axios.delete(`/api/customer/${storeSlug}/favorites/${productId}`, { headers: authHeaders() })
    } else {
      await axios.post(`/api/customer/${storeSlug}/favorites`, { product_id: productId }, { headers: authHeaders() })
    }

    await fetchFavorites()
  }

  async function fetchAddresses() {
    const response = await axios.get(`/api/customer/${storeSlug}/addresses`, { headers: authHeaders() })
    addresses.value = response.data?.data ?? []
    return addresses.value
  }

  async function saveAddress(payload: Record<string, any>, addressId?: number) {
    if (addressId) {
      await axios.put(`/api/customer/${storeSlug}/addresses/${addressId}`, payload, { headers: authHeaders() })
    } else {
      await axios.post(`/api/customer/${storeSlug}/addresses`, payload, { headers: authHeaders() })
    }

    await fetchAddresses()
  }

  async function deleteAddress(addressId: number) {
    await axios.delete(`/api/customer/${storeSlug}/addresses/${addressId}`, { headers: authHeaders() })
    await fetchAddresses()
  }

  return {
    loading,
    error,
    customer,
    token,
    cart,
    cartTotals,
    favorites,
    orders,
    addresses,
    isAuthenticated,
    favoriteProductIds,
    cartItemsCount,
    bootstrap,
    register,
    login,
    logout,
    fetchMe,
    lookupZip,
    fetchCart,
    addToCart,
    updateCartItem,
    removeCartItem,
    clearCart,
    checkout,
    fetchOrders,
    repeatOrder,
    fetchOrderWhatsappLink,
    fetchFavorites,
    toggleFavorite,
    fetchAddresses,
    saveAddress,
    deleteAddress,
  }
}
