<script setup>
import DefaultLayout from '@/components/layout/DefaultLayout.vue'
import { ref, computed, onMounted } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import api from '@/lib/axios'
import { useCartStore } from '@/stores/cart'
import { useI18n } from 'vue-i18n'
import { useCurrency } from '@/composables/useCurrency'
import { useToast } from '@/composables/useToast'
import { useMeta } from '@/composables/useMeta'

const { t } = useI18n()
const { formatPrice } = useCurrency()
const toast = useToast()
const route = useRoute()
const cart = useCartStore()
const product = ref(null)

useMeta(computed(() => product.value?.name))
const quantity = ref(1)
const loading = ref(true)
const added = ref(false)
const loadError = ref(false)

async function loadProduct() {
  loading.value = true
  loadError.value = false
  try {
    const { data } = await api.get(`/products/${route.params.id}`)
    product.value = data.data
  } catch {
    loadError.value = true
    toast.error(t('toast.load_error'))
  } finally {
    loading.value = false
  }
}

onMounted(loadProduct)

function addToCart() {
  if (!product.value) return
  cart.addItem(
    { id: product.value.id, name: product.value.name, price: Number(product.value.price), image_url: product.value.image_url },
    quantity.value
  )
  toast.success(t('toast.added_to_cart'))
  added.value = true
  setTimeout(() => { added.value = false }, 2000)
}
</script>

<template>
  <DefaultLayout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Loading skeleton -->
      <div v-if="loading" class="grid grid-cols-1 lg:grid-cols-2 gap-10 animate-pulse">
        <div class="bg-gray-200 dark:bg-gray-700 rounded-xl h-96"></div>
        <div class="space-y-4 py-4">
          <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-24"></div>
          <div class="h-8 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
          <div class="h-8 bg-gray-200 dark:bg-gray-700 rounded w-32"></div>
          <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-40"></div>
          <div class="h-20 bg-gray-200 dark:bg-gray-700 rounded"></div>
          <div class="h-12 bg-gray-200 dark:bg-gray-700 rounded w-full"></div>
        </div>
      </div>

      <!-- Error state -->
      <div v-else-if="loadError" class="text-center py-20">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
        <p class="text-gray-500 dark:text-gray-400 text-lg mb-4">{{ t('toast.load_error') }}</p>
        <button @click="loadProduct" class="text-indigo-600 dark:text-indigo-400 font-medium cursor-pointer">{{ t('common.retry') }}</button>
      </div>

      <template v-else-if="product">
        <!-- Breadcrumb -->
        <nav class="mb-6 text-sm text-gray-500 dark:text-gray-400">
          <RouterLink to="/products" class="hover:text-indigo-600 dark:hover:text-indigo-400">{{ t('products.title') }}</RouterLink>
          <span class="mx-2">/</span>
          <span class="text-gray-900 dark:text-gray-100">{{ product.name }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
          <!-- Image -->
          <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 h-96 flex items-center justify-center overflow-hidden shadow-sm">
            <img v-if="product.image_url" :src="product.image_url" :alt="product.name" loading="lazy" class="h-full w-full object-cover hover:scale-105 transition-transform duration-300" />
            <span v-else class="text-gray-400 dark:text-gray-500">{{ t('products.no_image') }}</span>
          </div>

          <!-- Info -->
          <div>
            <p class="text-sm text-indigo-600 dark:text-indigo-400 font-medium mb-2">{{ product.category?.name }}</p>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-4">{{ product.name }}</h1>
            <p class="text-3xl font-extrabold text-gray-900 dark:text-gray-100 mb-4">{{ formatPrice(product.price) }}</p>

            <div class="mb-6">
              <span v-if="product.stock > 0" class="text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/30 px-3 py-1 rounded-full text-sm font-medium">
                {{ t('products.in_stock') }} ({{ t('products.available', { count: product.stock }) }})
              </span>
              <span v-else class="text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30 px-3 py-1 rounded-full text-sm font-medium">{{ t('products.out_of_stock') }}</span>
            </div>

            <p class="text-gray-600 dark:text-gray-400 mb-6 leading-relaxed">{{ product.description }}</p>

            <div v-if="product.stock > 0" class="flex items-center gap-4">
              <div class="flex items-center border border-gray-300 dark:border-gray-600 rounded-lg">
                <button @click="quantity > 1 && quantity--" aria-label="Decrease quantity" class="px-3 py-2 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">-</button>
                <span class="px-4 py-2 font-medium">{{ quantity }}</span>
                <button @click="quantity < product.stock && quantity++" aria-label="Increase quantity" class="px-3 py-2 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">+</button>
              </div>
              <button
                @click="addToCart"
                class="flex-1 bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 transition cursor-pointer"
              >
                {{ added ? t('products.added') : t('products.add_to_cart') }}
              </button>
            </div>
          </div>
        </div>
      </template>
    </div>
  </DefaultLayout>
</template>
