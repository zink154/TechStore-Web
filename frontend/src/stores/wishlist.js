import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/lib/axios'

export const useWishlistStore = defineStore('wishlist', () => {
  const items = ref(new Set())
  const count = computed(() => items.value.size)

  async function checkProducts(productIds) {
    if (!productIds.length) return
    try {
      const { data } = await api.get('/wishlist/check', { params: { product_ids: productIds.join(',') } })
      items.value = new Set(data.data)
    } catch { /* silent */ }
  }

  async function toggle(productId) {
    const { data } = await api.post(`/wishlist/${productId}/toggle`)
    if (data.data.wishlisted) {
      items.value = new Set([...items.value, productId])
    } else {
      const next = new Set(items.value)
      next.delete(productId)
      items.value = next
    }
    return data.data.wishlisted
  }

  function isWishlisted(productId) {
    return items.value.has(productId)
  }

  function clear() {
    items.value = new Set()
  }

  return { items, count, checkProducts, toggle, isWishlisted, clear }
})
