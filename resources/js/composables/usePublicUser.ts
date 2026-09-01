import { ref } from 'vue'
import axios from 'axios'

type PublicUser = Record<string, any> | null

const userCacheBySlug = new Map<string, PublicUser>()
export const publicUser = ref<PublicUser>(null)

export function clearPublicUser(slug?: string) {
  if (slug) {
    userCacheBySlug.delete(slug)
  } else {
    userCacheBySlug.clear()
  }

  publicUser.value = null
}

export async function loadPublicUser(slug: string, force = false) {
  if (!force && userCacheBySlug.has(slug)) {
    const cached = userCacheBySlug.get(slug) ?? null
    publicUser.value = cached
    return cached
  }

  const response = await axios.get(`/api/v1/users/${slug}`)
  const user = response.data?.data ?? response.data

  userCacheBySlug.set(slug, user)
  publicUser.value = user

  return user
}
