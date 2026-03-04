<script setup>
import DefaultLayout from '@/components/layout/DefaultLayout.vue'
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/lib/axios'
import { useCartStore } from '@/stores/cart'
import { useI18n } from 'vue-i18n'
import { useCurrency } from '@/composables/useCurrency'

const { t } = useI18n()
const { formatPrice } = useCurrency()
const cart = useCartStore()
const featuredProducts = ref([])
const categories = ref([])
const loading = ref(true)

const categoryIcons = {
  laptops: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25A2.25 2.25 0 0 1 5.25 3h13.5A2.25 2.25 0 0 1 21 5.25Z"/></svg>`,
  smartphones: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/></svg>`,
  tablets: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5h3m-6.75 2.25h10.5a2.25 2.25 0 0 0 2.25-2.25V4.5a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 4.5v15a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>`,
  accessories: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8"><path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z"/></svg>`,
  audio: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8"><path stroke-linecap="round" stroke-linejoin="round" d="M19.114 5.636a9 9 0 0 1 0 12.728M16.463 8.288a5.25 5.25 0 0 1 0 7.424M6.75 8.25l4.72-4.72a.75.75 0 0 1 1.28.53v15.88a.75.75 0 0 1-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.009 9.009 0 0 1 2.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75Z"/></svg>`,
  gaming: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8"><path stroke-linecap="round" stroke-linejoin="round" d="M14.25 6.087c0-.355.186-.676.401-.959.221-.29.349-.634.349-1.003 0-1.036-1.007-1.875-2.25-1.875s-2.25.84-2.25 1.875c0 .369.128.713.349 1.003.215.283.401.604.401.959v0a.64.64 0 0 1-.657.643 48.39 48.39 0 0 1-4.163-.3c.186 1.613.293 3.25.315 4.907a.656.656 0 0 1-.658.663v0c-.355 0-.676-.186-.959-.401a1.647 1.647 0 0 0-1.003-.349c-1.036 0-1.875 1.007-1.875 2.25s.84 2.25 1.875 2.25c.369 0 .713-.128 1.003-.349.283-.215.604-.401.959-.401v0c.31 0 .555.26.532.57a48.039 48.039 0 0 1-.642 5.056c1.518.19 3.058.309 4.616.354a.64.64 0 0 0 .657-.643v0c0-.355-.186-.676-.401-.959a1.647 1.647 0 0 1-.349-1.003c0-1.035 1.008-1.875 2.25-1.875 1.243 0 2.25.84 2.25 1.875 0 .369-.128.713-.349 1.003-.215.283-.4.604-.4.959v0c0 .333.277.599.61.58a48.1 48.1 0 0 0 5.427-.63 48.05 48.05 0 0 0 .582-4.717.532.532 0 0 0-.533-.57v0c-.355 0-.676.186-.959.401-.29.221-.634.349-1.003.349-1.035 0-1.875-1.007-1.875-2.25s.84-2.25 1.875-2.25c.37 0 .713.128 1.003.349.283.215.604.401.959.401v0a.656.656 0 0 0 .658-.663 48.422 48.422 0 0 0-.37-5.36c-1.886.342-3.81.574-5.766.689a.578.578 0 0 1-.61-.58v0Z"/></svg>`,
}

const addedProducts = ref(new Set())

function addToCart(product) {
  cart.addItem({ id: product.id, name: product.name, price: Number(product.price), image_url: product.image_url })
  addedProducts.value = new Set([...addedProducts.value, product.id])
  setTimeout(() => {
    const next = new Set(addedProducts.value)
    next.delete(product.id)
    addedProducts.value = next
  }, 1500)
}

onMounted(async () => {
  const [productsRes, categoriesRes] = await Promise.all([
    api.get('/products', { params: { per_page: 8 } }),
    api.get('/categories'),
  ])
  featuredProducts.value = productsRes.data.data.data
  categories.value = categoriesRes.data.data
  loading.value = false
})
</script>

<template>
  <DefaultLayout>
    <!-- Hero -->
    <section class="relative bg-gradient-to-br from-indigo-600 via-purple-600 to-indigo-800 text-white overflow-hidden">
      <div class="absolute inset-0 opacity-10">
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-white rounded-full"></div>
        <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-white rounded-full"></div>
      </div>
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 sm:py-32">
        <div class="max-w-3xl">
          <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight mb-6">
            {{ t('hero.title_1') }}<br>
            <span class="text-indigo-200">{{ t('hero.title_2') }}</span>
          </h1>
          <p class="text-lg sm:text-xl text-indigo-100 mb-10 max-w-xl leading-relaxed">
            {{ t('hero.subtitle') }}
          </p>
          <div class="flex flex-wrap gap-4">
            <RouterLink to="/products" class="inline-flex items-center bg-white text-indigo-600 font-semibold px-8 py-3.5 rounded-lg hover:bg-indigo-50 transition shadow-lg">
              {{ t('hero.shop_now') }}
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </RouterLink>
            <RouterLink to="/products" class="inline-flex items-center border-2 border-white/30 text-white font-semibold px-8 py-3.5 rounded-lg hover:bg-white/10 transition">
              {{ t('hero.browse') }}
            </RouterLink>
          </div>
        </div>
      </div>
    </section>

    <!-- Trust badges -->
    <section class="bg-white border-b border-gray-100 dark:bg-gray-800 dark:border-gray-700">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
            </div>
            <div>
              <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ t('trust.free_shipping') }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">{{ t('trust.free_shipping_desc') }}</p>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
            </div>
            <div>
              <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ t('trust.secure_payment') }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">{{ t('trust.secure_payment_desc') }}</p>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182"/></svg>
            </div>
            <div>
              <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ t('trust.easy_returns') }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">{{ t('trust.easy_returns_desc') }}</p>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/></svg>
            </div>
            <div>
              <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ t('trust.support') }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">{{ t('trust.support_desc') }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Categories -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <div class="text-center mb-10">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">{{ t('home.shop_by_category') }}</h2>
        <p class="text-gray-500 dark:text-gray-400">{{ t('home.category_subtitle') }}</p>
      </div>
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <RouterLink
          v-for="cat in categories"
          :key="cat.id"
          :to="`/products?category=${cat.slug}`"
          class="group bg-white rounded-xl p-6 text-center shadow-sm hover:shadow-lg transition-all duration-200 border border-gray-100 hover:border-indigo-200 dark:bg-gray-800 dark:border-gray-700 dark:hover:border-indigo-500"
        >
          <div class="w-14 h-14 mx-auto mb-3 bg-indigo-50 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center text-indigo-600 group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/50 transition-colors" v-html="categoryIcons[cat.slug] || categoryIcons.accessories"></div>
          <p class="font-semibold text-gray-900 dark:text-gray-100 text-sm">{{ cat.name }}</p>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ cat.products_count }} {{ t('home.items') }}</p>
        </RouterLink>
      </div>
    </section>

    <!-- Featured Products -->
    <section class="bg-gray-50 dark:bg-gray-900">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="flex justify-between items-center mb-10">
          <div>
            <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">{{ t('home.featured') }}</h2>
            <p class="text-gray-500 dark:text-gray-400">{{ t('home.featured_subtitle') }}</p>
          </div>
          <RouterLink to="/products" class="hidden sm:inline-flex items-center text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">
            {{ t('home.view_all') }}
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </RouterLink>
        </div>

        <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div v-for="n in 8" :key="n" class="bg-white rounded-xl overflow-hidden border border-gray-100 dark:bg-gray-800 dark:border-gray-700 animate-pulse">
            <div class="h-52 bg-gray-200 dark:bg-gray-600"></div>
            <div class="p-4 space-y-3">
              <div class="h-3 bg-gray-200 dark:bg-gray-600 rounded w-16"></div>
              <div class="h-4 bg-gray-200 dark:bg-gray-600 rounded w-3/4"></div>
              <div class="h-5 bg-gray-200 dark:bg-gray-600 rounded w-20"></div>
            </div>
          </div>
        </div>

        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div
            v-for="product in featuredProducts"
            :key="product.id"
            class="group bg-white rounded-xl shadow-sm hover:shadow-lg transition-all duration-200 overflow-hidden border border-gray-100 dark:bg-gray-800 dark:border-gray-700"
          >
            <RouterLink :to="`/products/${product.slug}`" class="block">
              <div class="h-52 bg-gray-100 dark:bg-gray-700 overflow-hidden">
                <img
                  v-if="product.image_url"
                  :src="product.image_url"
                  :alt="product.name"
                  class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-300"
                />
                <div v-else class="h-full flex items-center justify-center text-gray-400 dark:text-gray-500 text-sm">{{ t('products.no_image') }}</div>
              </div>
            </RouterLink>
            <div class="p-4">
              <p class="text-xs text-indigo-600 dark:text-indigo-400 font-medium mb-1">{{ product.category?.name }}</p>
              <RouterLink :to="`/products/${product.slug}`">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100 truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ product.name }}</h3>
              </RouterLink>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-1">{{ product.description }}</p>
              <div class="flex justify-between items-center mt-3">
                <span class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ formatPrice(product.price) }}</span>
                <button
                  v-if="product.stock > 0"
                  @click="addToCart(product)"
                  :class="[
                    'text-xs px-3 py-1.5 rounded-lg font-medium transition cursor-pointer',
                    addedProducts.has(product.id)
                      ? 'bg-green-500 text-white'
                      : 'bg-indigo-600 text-white hover:bg-indigo-700'
                  ]"
                >
                  {{ addedProducts.has(product.id) ? t('products.added') : t('products.add_to_cart') }}
                </button>
                <span v-else class="text-xs text-red-500 dark:text-red-400 font-medium">{{ t('products.out_of_stock') }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="text-center mt-10 sm:hidden">
          <RouterLink to="/products" class="inline-flex items-center text-indigo-600 dark:text-indigo-400 font-medium">
            {{ t('home.view_all_products') }}
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </RouterLink>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="bg-indigo-600">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
        <h2 class="text-2xl sm:text-3xl font-bold text-white mb-4">{{ t('home.cta_title') }}</h2>
        <p class="text-indigo-100 mb-8 max-w-xl mx-auto">{{ t('home.cta_subtitle') }}</p>
        <RouterLink to="/register" class="inline-flex items-center bg-white text-indigo-600 font-semibold px-8 py-3.5 rounded-lg hover:bg-indigo-50 transition shadow-lg">
          {{ t('home.create_account') }}
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </RouterLink>
      </div>
    </section>
  </DefaultLayout>
</template>

<style scoped>
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
