<script setup>
import { ref, watch, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/lib/axios'
import { useI18n } from 'vue-i18n'
import { useCurrency } from '@/composables/useCurrency'
import { useCartStore } from '@/stores/cart'
import { useToast } from '@/composables/useToast'
import StarRating from './StarRating.vue'

const props = defineProps({
  categorySlug: { type: String, required: true },
  categoryName: { type: String, default: '' },
  currentProductId: { type: Number, required: true },
})

const { t } = useI18n()
const { formatPrice } = useCurrency()
const cart = useCartStore()
const toast = useToast()
const products = ref([])
const loading = ref(true)

async function fetchRelated() {
  loading.value = true
  try {
    const { data } = await api.get('/products', {
      params: { category: props.categorySlug, per_page: 5 }
    })
    products.value = data.data.data
      .filter(p => p.id !== props.currentProductId)
      .slice(0, 4)
  } catch { /* silent */ }
  finally { loading.value = false }
}

onMounted(fetchRelated)
watch(() => props.currentProductId, fetchRelated)

function addToCart(product) {
  cart.addItem({ id: product.id, name: product.name, price: Number(product.price), image_url: product.image_url })
  toast.success(t('toast.added_to_cart'))
}
</script>

<template>
  <section v-if="!loading && products.length">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ t('related.title') }}</h2>
      <RouterLink
        :to="`/products?category=${categorySlug}`"
        class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 text-sm font-medium"
      >
        {{ t('related.view_all', { category: categoryName }) }}
      </RouterLink>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <div
        v-for="product in products"
        :key="product.id"
        class="group bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-lg transition-all duration-200 overflow-hidden border border-gray-100 dark:border-gray-700"
      >
        <RouterLink :to="`/products/${product.slug}`">
          <div class="h-48 bg-gray-100 dark:bg-gray-700 overflow-hidden">
            <img v-if="product.image_url" :src="product.image_url" :alt="product.name" loading="lazy" class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-300" />
            <div v-else class="h-full flex items-center justify-center text-gray-400 dark:text-gray-500 text-sm">{{ t('products.no_image') }}</div>
          </div>
        </RouterLink>
        <div class="p-4">
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
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
