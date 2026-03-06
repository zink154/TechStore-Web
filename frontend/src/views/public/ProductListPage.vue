<script setup>
import DefaultLayout from '@/components/layout/DefaultLayout.vue'
import StarRating from '@/components/common/StarRating.vue'
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import api from '@/lib/axios'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import { useWishlistStore } from '@/stores/wishlist'
import { useI18n } from 'vue-i18n'
import { useCurrency } from '@/composables/useCurrency'
import { useToast } from '@/composables/useToast'
import { useDebounce } from '@/composables/useDebounce'
import { useMeta } from '@/composables/useMeta'

const { t } = useI18n()
const { formatPrice } = useCurrency()
const toast = useToast()
const route = useRoute()
const router = useRouter()
const cart = useCartStore()
const auth = useAuthStore()
const wishlist = useWishlistStore()
const loadError = ref(false)
const searchInput = ref(route.query.search ?? '')
const debouncedSearch = useDebounce(searchInput, 400)

useMeta(computed(() => t('products.title')))

const products = ref([])
const categories = ref([])
const pagination = ref({})
const loading = ref(false)

const filters = ref({
  search: route.query.search ?? '',
  category: route.query.category ?? '',
  sort: route.query.sort ?? 'created_at',
  order: route.query.order ?? 'desc',
  page: Number(route.query.page) || 1,
  min_price: route.query.min_price ?? '',
  max_price: route.query.max_price ?? '',
})

async function fetchProducts() {
  loading.value = true
  loadError.value = false
  const params = { ...filters.value, per_page: 12 }
  Object.keys(params).forEach((k) => { if (!params[k]) delete params[k] })

  try {
    const { data } = await api.get('/products', { params })
    products.value = data.data.data
    pagination.value = {
      current_page: data.data.current_page,
      last_page: data.data.last_page,
      total: data.data.total,
    }
    if (auth.isAuthenticated && products.value.length) {
      wishlist.checkProducts(products.value.map(p => p.id))
    }
  } catch {
    loadError.value = true
    toast.error(t('toast.load_error'))
  } finally {
    loading.value = false
  }
}

async function fetchCategories() {
  try {
    const { data } = await api.get('/categories')
    categories.value = data.data
  } catch { /* silent */ }
}

function applyFilters() {
  filters.value.page = 1
  updateQuery()
}

function goToPage(page) {
  filters.value.page = page
  updateQuery()
}

function updateQuery() {
  const query = {}
  if (filters.value.search) query.search = filters.value.search
  if (filters.value.category) query.category = filters.value.category
  if (filters.value.sort !== 'created_at') query.sort = filters.value.sort
  if (filters.value.order !== 'desc') query.order = filters.value.order
  if (filters.value.page > 1) query.page = filters.value.page
  if (filters.value.min_price) query.min_price = filters.value.min_price
  if (filters.value.max_price) query.max_price = filters.value.max_price
  router.push({ path: '/products', query })
}

function addToCart(product) {
  cart.addItem({ id: product.id, name: product.name, price: Number(product.price), image_url: product.image_url })
  toast.success(t('toast.added_to_cart'))
}

async function toggleWishlist(product) {
  try {
    const wishlisted = await wishlist.toggle(product.id)
    toast.success(wishlisted ? t('wishlist.added') : t('wishlist.removed'))
  } catch {
    toast.error(t('common.error'))
  }
}

watch(debouncedSearch, (val) => {
  if (val !== filters.value.search) {
    filters.value.search = val
    applyFilters()
  }
})

watch(() => route.query, () => {
  filters.value.search = route.query.search ?? ''
  searchInput.value = filters.value.search
  filters.value.category = route.query.category ?? ''
  filters.value.sort = route.query.sort ?? 'created_at'
  filters.value.order = route.query.order ?? 'desc'
  filters.value.page = Number(route.query.page) || 1
  filters.value.min_price = route.query.min_price ?? ''
  filters.value.max_price = route.query.max_price ?? ''
  fetchProducts()
})

onMounted(() => {
  fetchCategories()
  fetchProducts()
})
</script>

<template>
  <DefaultLayout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-6">{{ t('products.title') }}</h1>

      <!-- Filters -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 flex flex-wrap gap-4 items-end border border-gray-100 dark:border-gray-700">
        <div class="flex-1 min-w-[200px]">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('products.search') }}</label>
          <input
            v-model="searchInput"
            type="text"
            :placeholder="t('products.search_placeholder')"
            class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
            @keyup.enter="applyFilters"
          />
        </div>
        <div class="min-w-[150px]">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('products.category') }}</label>
          <select v-model="filters.category" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" @change="applyFilters">
            <option value="">{{ t('products.all_categories') }}</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.slug">{{ cat.name }}</option>
          </select>
        </div>
        <div class="min-w-[110px]">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('products.min_price') }}</label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">$</span>
            <input v-model="filters.min_price" type="number" min="0" step="0.01" :placeholder="t('products.min')"
                   class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg pl-7 pr-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                   @change="applyFilters" />
          </div>
        </div>
        <div class="min-w-[110px]">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('products.max_price') }}</label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">$</span>
            <input v-model="filters.max_price" type="number" min="0" step="0.01" :placeholder="t('products.max')"
                   class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg pl-7 pr-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                   @change="applyFilters" />
          </div>
        </div>
        <div class="min-w-[150px]">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('products.sort_by') }}</label>
          <select v-model="filters.sort" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" @change="applyFilters">
            <option value="created_at">{{ t('products.newest') }}</option>
            <option value="price">{{ t('products.price') }}</option>
            <option value="name">{{ t('products.name') }}</option>
          </select>
        </div>
        <button @click="applyFilters" class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 cursor-pointer">
          {{ t('products.filter') }}
        </button>
      </div>

      <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ t('products.found', { count: pagination.total ?? 0 }) }}</p>

      <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div v-for="n in 8" :key="n" class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden border border-gray-100 dark:border-gray-700 animate-pulse">
          <div class="h-48 bg-gray-200 dark:bg-gray-600"></div>
          <div class="p-4 space-y-3">
            <div class="h-3 bg-gray-200 dark:bg-gray-600 rounded w-16"></div>
            <div class="h-4 bg-gray-200 dark:bg-gray-600 rounded w-3/4"></div>
            <div class="h-5 bg-gray-200 dark:bg-gray-600 rounded w-20"></div>
          </div>
        </div>
      </div>

      <div v-else-if="products.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div
          v-for="product in products"
          :key="product.id"
          class="group bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-lg transition-all duration-200 overflow-hidden border border-gray-100 dark:border-gray-700"
        >
          <RouterLink :to="`/products/${product.slug}`" class="relative block">
            <div class="h-48 bg-gray-100 dark:bg-gray-700 overflow-hidden">
              <img v-if="product.image_url" :src="product.image_url" :alt="product.name" loading="lazy" class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-300" />
              <div v-else class="h-full flex items-center justify-center text-gray-400 dark:text-gray-500 text-sm">{{ t('products.no_image') }}</div>
            </div>
            <button
              v-if="auth.isAuthenticated"
              @click.prevent="toggleWishlist(product)"
              :aria-label="wishlist.isWishlisted(product.id) ? t('wishlist.remove') : t('wishlist.add')"
              class="absolute top-2 right-2 p-1.5 rounded-full bg-white/80 dark:bg-gray-800/80 hover:bg-white dark:hover:bg-gray-800 transition cursor-pointer"
            >
              <svg xmlns="http://www.w3.org/2000/svg"
                   :fill="wishlist.isWishlisted(product.id) ? 'currentColor' : 'none'"
                   viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                   :class="['w-5 h-5', wishlist.isWishlisted(product.id) ? 'text-red-500' : 'text-gray-400 hover:text-red-500']">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/>
              </svg>
            </button>
          </RouterLink>
          <div class="p-4">
            <p class="text-xs text-indigo-600 dark:text-indigo-400 font-medium mb-1">{{ product.category?.name }}</p>
            <RouterLink :to="`/products/${product.slug}`">
              <h3 class="font-semibold text-gray-900 dark:text-gray-100 truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ product.name }}</h3>
            </RouterLink>
            <div v-if="product.reviews_avg_rating" class="flex items-center gap-1 mt-1">
              <StarRating :value="Math.round(Number(product.reviews_avg_rating))" readonly size="sm" />
              <span class="text-xs text-gray-500 dark:text-gray-400">({{ product.reviews_count }})</span>
            </div>
            <div class="flex justify-between items-center mt-3">
              <span class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ formatPrice(product.price) }}</span>
              <button
                v-if="product.stock > 0"
                @click="addToCart(product)"
                class="bg-indigo-600 text-white text-xs px-3 py-1.5 rounded-lg hover:bg-indigo-700 cursor-pointer"
              >
                {{ t('products.add_to_cart') }}
              </button>
              <span v-else class="text-xs text-red-500 dark:text-red-400">{{ t('products.out_of_stock') }}</span>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="text-center py-16">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <p class="text-gray-500 dark:text-gray-400 text-lg mb-2">{{ t('products.no_products') }}</p>
        <button @click="filters.search = ''; filters.category = ''; filters.min_price = ''; filters.max_price = ''; applyFilters()" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 font-medium cursor-pointer">{{ t('products.clear_filters') }}</button>
      </div>

      <div v-if="pagination.last_page > 1" class="flex justify-center mt-8 space-x-2">
        <button
          v-for="page in pagination.last_page"
          :key="page"
          @click="goToPage(page)"
          :class="[
            'px-3 py-1 rounded text-sm cursor-pointer',
            page === pagination.current_page
              ? 'bg-indigo-600 text-white'
              : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'
          ]"
        >
          {{ page }}
        </button>
      </div>
    </div>
  </DefaultLayout>
</template>
