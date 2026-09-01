import { computed, ref, watch } from 'vue'

type GuestItem = {
  id: string
  product_id: number
  quantity: number
  unit_price: number
  total_price: number
  product: any
}

function storageKey(storeSlug: string): string {
  return `guest_cart:${storeSlug}`
}

export function useGuestStoreCart(storeSlug: string) {
  const items = ref<GuestItem[]>([])

  const loaded = localStorage.getItem(storageKey(storeSlug))
  if (loaded) {
    try {
      items.value = JSON.parse(loaded)
    } catch {
      items.value = []
    }
  }

  watch(
    items,
    (value) => {
      localStorage.setItem(storageKey(storeSlug), JSON.stringify(value))
    },
    { deep: true },
  )

  const totals = computed(() => {
    const subtotal = items.value.reduce((sum, item) => sum + Number(item.total_price || 0), 0)
    const count = items.value.reduce((sum, item) => sum + Number(item.quantity || 0), 0)

    return {
      subtotal,
      total: subtotal,
      items_count: count,
    }
  })

  const cart = computed(() => ({
    items: items.value,
  }))

  function addProduct(product: any, quantity = 1) {
    const productId = Number(product.id)
    if (!productId) return

    const price = Number(product.discount_price > 0 ? product.discount_price : product.price)
    const existing = items.value.find((item) => item.product_id === productId)

    if (existing) {
      existing.quantity += quantity
      existing.total_price = existing.quantity * Number(existing.unit_price)
      return
    }

    items.value.push({
      id: `guest-${productId}`,
      product_id: productId,
      quantity,
      unit_price: price,
      total_price: quantity * price,
      product,
    })
  }

  function updateItem(itemId: string | number, quantity: number) {
    const index = items.value.findIndex((item) => item.id === itemId || item.product_id === Number(itemId))
    if (index < 0) return

    if (quantity <= 0) {
      items.value.splice(index, 1)
      return
    }

    items.value[index].quantity = quantity
    items.value[index].total_price = quantity * Number(items.value[index].unit_price)
  }

  function removeItem(itemId: string | number) {
    const index = items.value.findIndex((item) => item.id === itemId || item.product_id === Number(itemId))
    if (index >= 0) {
      items.value.splice(index, 1)
    }
  }

  function clear() {
    items.value = []
  }

  function toSyncPayload() {
    return items.value.map((item) => ({ product_id: item.product_id, quantity: item.quantity }))
  }

  return {
    cart,
    items,
    totals,
    addProduct,
    updateItem,
    removeItem,
    clear,
    toSyncPayload,
  }
}
