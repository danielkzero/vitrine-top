// src/composables/useVitrine.ts
import axios from 'axios'
import { ref, computed, UnwrapRef } from 'vue'

/**
 * Composable para carregar dados da vitrine via API (REST).
 * - Usa cache simples em memória por slug
 * - Faz carregamento condicional (pages => only load products/reviews/categories when needed)
 * - Retorna métodos de reload e helpers
 */

// Tipagens mínimas (adicione mais campos conforme necessidade)
export interface User {
  id: number
  slug: string
  business_name: string
  subtitle?: string | null
  logo_path?: string | null
  background_path?: string | null
  theme_color?: string | null
}

export interface Page {
  id: number
  key: string
  title: string
  icon?: string | null
  type: string
  content?: any
  is_active?: boolean
  order?: number
}

export interface Product {
  id: number
  name: string
  price?: number
  cover_image?: string | null
  // ... mais campos conforme ProductResource
}

export interface Category {
  id: number
  name: string
  slug: string
}

export interface Banner {
  id: number
  title?: string
  image?: string | null // base64 or url
  order?: number
}

export interface Review {
  id: number
  rating: number
  comment?: string
  customer_name?: string
}

type CacheBucket = {
  user?: User | null
  pages?: Page[]
  products?: Product[]
  categories?: Category[]
  banners?: Banner[]
  reviews?: Review[]
}

const memoryCache = new Map<string, CacheBucket>() // key = slug

export function useVitrineApi() {
  const loading = ref(false)
  const error = ref<string | null>(null)

  async function fetchUser(slug: string): Promise<User> {
    const cache = memoryCache.get(slug)
    if (cache?.user) return cache.user

    try {
      loading.value = true
      const res = await axios.get(`/v1/users/${encodeURIComponent(slug)}`)
      const user = res.data
      memoryCache.set(slug, { ...(memoryCache.get(slug) ?? {}), user })
      return user
    } catch (err: any) {
      error.value = err?.response?.data?.message || err.message || 'Erro ao carregar usuário'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchPages(slug: string): Promise<Page[]> {
    const cache = memoryCache.get(slug)
    if (cache?.pages) return cache.pages

    try {
      loading.value = true
      const res = await axios.get(`/v1/users/${encodeURIComponent(slug)}/pages`)
      const pages: Page[] = res.data
      memoryCache.set(slug, { ...(memoryCache.get(slug) ?? {}), pages })
      return pages
    } catch (err: any) {
      error.value = err?.response?.data?.message || err.message || 'Erro ao carregar páginas'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchProducts(slug: string): Promise<Product[]> {
    const cache = memoryCache.get(slug)
    if (cache?.products) return cache.products

    try {
      loading.value = true
      const res = await axios.get(`/v1/users/${encodeURIComponent(slug)}/products`)
      const products: Product[] = res.data?.data ?? res.data // paginate or plain list
      memoryCache.set(slug, { ...(memoryCache.get(slug) ?? {}), products })
      return products
    } catch (err: any) {
      error.value = err?.response?.data?.message || err.message || 'Erro ao carregar produtos'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchProductById(slug: string, id: number) {
    try {
      loading.value = true
      const res = await axios.get(`/v1/users/${encodeURIComponent(slug)}/products/${id}`)
      return res.data
    } catch (err: any) {
      error.value = err?.response?.data?.message || err.message || 'Erro ao carregar produto'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchCategories(slug: string): Promise<Category[]> {
    const cache = memoryCache.get(slug)
    if (cache?.categories) return cache.categories

    try {
      loading.value = true
      const res = await axios.get(`/v1/users/${encodeURIComponent(slug)}/categories`)
      const categories = res.data
      memoryCache.set(slug, { ...(memoryCache.get(slug) ?? {}), categories })
      return categories
    } catch (err: any) {
      error.value = err?.response?.data?.message || err.message || 'Erro ao carregar categorias'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchBanners(slug: string): Promise<Banner[]> {
    const cache = memoryCache.get(slug)
    if (cache?.banners) return cache.banners

    try {
      loading.value = true
      const res = await axios.get(`/v1/users/${encodeURIComponent(slug)}/banners`)
      const banners = res.data
      memoryCache.set(slug, { ...(memoryCache.get(slug) ?? {}), banners })
      return banners
    } catch (err: any) {
      error.value = err?.response?.data?.message || err.message || 'Erro ao carregar banners'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchReviews(slug: string): Promise<Review[]> {
    const cache = memoryCache.get(slug)
    if (cache?.reviews) return cache.reviews

    try {
      loading.value = true
      const res = await axios.get(`/v1/users/${encodeURIComponent(slug)}/reviews`)
      const reviews = res.data?.data ?? res.data
      memoryCache.set(slug, { ...(memoryCache.get(slug) ?? {}), reviews })
      return reviews
    } catch (err: any) {
      error.value = err?.response?.data?.message || err.message || 'Erro ao carregar reviews'
      throw err
    } finally {
      loading.value = false
    }
  }

  function clearCache(slug?: string) {
    if (slug) memoryCache.delete(slug)
    else memoryCache.clear()
  }

  return {
    loading: computed(() => loading.value),
    error: computed(() => error.value),
    fetchUser,
    fetchPages,
    fetchProducts,
    fetchProductById,
    fetchCategories,
    fetchBanners,
    fetchReviews,
    clearCache,
  }
}
