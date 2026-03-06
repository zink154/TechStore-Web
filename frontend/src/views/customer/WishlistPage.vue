<script setup>
import DefaultLayout from '@/components/layout/DefaultLayout.vue'
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/lib/axios'
import { useCartStore } from '@/stores/cart'
import { useWishlistStore } from '@/stores/wishlist'
import { useI18n } from 'vue-i18n'
import { useCurrency } from '@/composables/useCurrency'
import { useToast } from '@/composables/useToast'
import { useMeta } from '@/composables/useMeta'
import { computed } from 'vue'

const { t } = useI18n()
const { formatPrice } = useCurrency()
const toast = useToast()
const cart = useCartStore()
const wishlist = useWishlistStore()
useMeta(computed(() => t('wishlist.title')))

const items = ref([])
const loading = ref(true)

async function fetchWishlist() {
  loading.value = true
  try {
    const { data } = await api.get('/wishlist')
    items.value = data.data.data
  } catch {
    toast.error(t('toast.load_error'))
  } finally {
    loading.value = false
  }
}

async function removeItem(productId) {
  try {
    await wishlist.toggle(productId)
    items.value = items.value.filter(w => w.product_id !== productId)
    toast.info(t('wishlist.removed'))
  } catch {
    toast.error(t('common.error'))
  }
}

function addToCart(product) {
  cart.addItem({ id: product.id, name: product.name, price: Number(product.price), image_url: product.image_url })
  toast.success(t('toast.added_to_cart'))
}

onMounted(fetchWishlist)
</script>

<template>
  <DefaultLayout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-6">{{ t('wishlist.title') }}</h1>

      <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div v-for="n in 4" :key="n" class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden border border-gray-100 dark:border-gray-700 animate-pulse">
          <div class="h-48 bg-gray-200 dark:bg-gray-600"></div>
          <div class="p-4 space-y-3">
            <div class="h-4 bg-gray-200 dark:bg-gray-600 rounded w-3/4"></div>
            <div class="h-5 bg-gray-200 dark:bg-gray-600 rounded w-20"></div>
          </div>
        </div>
      </div>

      <div v-else-if="items.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div
          v-for="item in items"
          :key="item.id"
          class="group bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-lg transition-all duration-200 overflow-hidden border border-gray-100 dark:border-gray-700"
        >
          <RouterLink :to="`/products/${item.product?.slug}`">
            <div class="h-48 bg-gray-100 dark:bg-gray-700 overflow-hidden">
              <img v-if="item.product?.image_url" :src="item.product.image_url" :alt="item.product.name" loading="lazy" class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-300" />
              <div v-else class="h-full flex items-center justify-center text-gray-400 dark:text-gray-500 text-sm">{{ t('products.no_image') }}</div>
            </div>
          </RouterLink>
          <div class="p-4">
            <p class="text-xs text-indigo-600 dark:text-indigo-400 font-medium mb-1">{{ item.product?.category?.name }}</p>
            <RouterLink :to="`/products/${item.product?.slug}`">
              <h3 class="font-semibold text-gray-900 dark:text-gray-100 truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ item.product?.name }}</h3>
            </RouterLink>
            <div class="flex justify-between items-center mt-3">
              <span class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ formatPrice(item.product?.price) }}</span>
              <div class="flex items-center gap-2">
                <button
                  v-if="item.product?.stock > 0"
                  @click="addToCart(item.product)"
                  class="bg-indigo-600 text-white text-xs px-3 py-1.5 rounded-lg hover:bg-indigo-700 cursor-pointer"
                >
                  {{ t('products.add_to_cart') }}
                </button>
                <button
                  @click="removeItem(item.product_id)"
                  class="text-red-500 hover:text-red-700 p-1.5 cursor-pointer"
                  :aria-label="t('wishlist.remove')"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="text-center py-16">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/>
        </svg>
        <p class="text-gray-500 dark:text-gray-400 text-lg mb-4">{{ t('wishlist.empty') }}</p>
        <RouterLink to="/products" class="text-indigo-600 dark:text-indigo-400 font-medium hover:text-indigo-700">{{ t('wishlist.browse') }}</RouterLink>
      </div>
    </div>
  </DefaultLayout>
</template>
