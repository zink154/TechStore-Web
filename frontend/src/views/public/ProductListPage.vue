<script setup>
import DefaultLayout from '@/components/layout/DefaultLayout.vue'
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import api from '@/lib/axios'
import { useCartStore } from '@/stores/cart'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const cart = useCartStore()

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
})

async function fetchProducts() {
  loading.value = true
  const params = { ...filters.value, per_page: 12 }
  Object.keys(params).forEach((k) => { if (!params[k]) delete params[k] })

  const { data } = await api.get('/products', { params })
  products.value = data.data.data
  pagination.value = {
    current_page: data.data.current_page,
    last_page: data.data.last_page,
    total: data.data.total,
  }
  loading.value = false
}

async function fetchCategories() {
  const { data } = await api.get('/categories')
  categories.value = data.data
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
  router.push({ path: '/products', query })
}

function addToCart(product) {
  cart.addItem({ id: product.id, name: product.name, price: Number(product.price), image_url: product.image_url })
}

watch(() => route.query, () => {
  filters.value.search = route.query.search ?? ''
  filters.value.category = route.query.category ?? ''
  filters.value.sort = route.query.sort ?? 'created_at'
  filters.value.order = route.query.order ?? 'desc'
  filters.value.page = Number(route.query.page) || 1
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
      <h1 class="text-3xl font-bold text-gray-900 mb-6">{{ t('products.title') }}</h1>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-sm p-4 mb-6 flex flex-wrap gap-4 items-end border border-gray-100">
        <div class="flex-1 min-w-[200px]">
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('products.search') }}</label>
          <input
            v-model="filters.search"
            type="text"
            :placeholder="t('products.search_placeholder')"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
            @keyup.enter="applyFilters"
          />
        </div>
        <div class="min-w-[150px]">
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('products.category') }}</label>
          <select v-model="filters.category" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" @change="applyFilters">
            <option value="">{{ t('products.all_categories') }}</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.slug">{{ cat.name }}</option>
          </select>
        </div>
        <div class="min-w-[150px]">
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('products.sort_by') }}</label>
          <select v-model="filters.sort" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" @change="applyFilters">
            <option value="created_at">{{ t('products.newest') }}</option>
            <option value="price">{{ t('products.price') }}</option>
            <option value="name">{{ t('products.name') }}</option>
          </select>
        </div>
        <button @click="applyFilters" class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 cursor-pointer">
          {{ t('products.filter') }}
        </button>
      </div>

      <!-- Results count -->
      <p class="text-sm text-gray-500 mb-4">{{ t('products.found', { count: pagination.total ?? 0 }) }}</p>

      <!-- Loading -->
      <div v-if="loading" class="text-center py-12 text-gray-500">{{ t('products.loading') }}</div>

      <!-- Products Grid -->
      <div v-else-if="products.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div
          v-for="product in products"
          :key="product.id"
          class="group bg-white rounded-xl shadow-sm hover:shadow-lg transition-all duration-200 overflow-hidden border border-gray-100"
        >
          <RouterLink :to="`/products/${product.slug}`">
            <div class="h-48 bg-gray-100 overflow-hidden">
              <img v-if="product.image_url" :src="product.image_url" :alt="product.name" class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-300" />
              <div v-else class="h-full flex items-center justify-center text-gray-400 text-sm">{{ t('products.no_image') }}</div>
            </div>
          </RouterLink>
          <div class="p-4">
            <p class="text-xs text-indigo-600 font-medium mb-1">{{ product.category?.name }}</p>
            <RouterLink :to="`/products/${product.slug}`">
              <h3 class="font-semibold text-gray-900 truncate group-hover:text-indigo-600 transition-colors">{{ product.name }}</h3>
            </RouterLink>
            <div class="flex justify-between items-center mt-3">
              <span class="text-lg font-bold text-gray-900">${{ product.price }}</span>
              <button
                v-if="product.stock > 0"
                @click="addToCart(product)"
                class="bg-indigo-600 text-white text-xs px-3 py-1.5 rounded-lg hover:bg-indigo-700 cursor-pointer"
              >
                {{ t('products.add_to_cart') }}
              </button>
              <span v-else class="text-xs text-red-500">{{ t('products.out_of_stock') }}</span>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="text-center py-12 text-gray-500">{{ t('products.no_products') }}</div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="flex justify-center mt-8 space-x-2">
        <button
          v-for="page in pagination.last_page"
          :key="page"
          @click="goToPage(page)"
          :class="[
            'px-3 py-1 rounded text-sm cursor-pointer',
            page === pagination.current_page
              ? 'bg-indigo-600 text-white'
              : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'
          ]"
        >
          {{ page }}
        </button>
      </div>
    </div>
  </DefaultLayout>
</template>
