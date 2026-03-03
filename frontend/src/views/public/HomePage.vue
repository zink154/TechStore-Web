<script setup>
import DefaultLayout from '@/components/layout/DefaultLayout.vue'
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/lib/axios'

const featuredProducts = ref([])
const categories = ref([])

onMounted(async () => {
  const [productsRes, categoriesRes] = await Promise.all([
    api.get('/products', { params: { per_page: 8 } }),
    api.get('/categories'),
  ])
  featuredProducts.value = productsRes.data.data.data
  categories.value = categoriesRes.data.data
})
</script>

<template>
  <DefaultLayout>
    <!-- Hero -->
    <section class="bg-gradient-to-r from-indigo-600 to-purple-700 text-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
        <h1 class="text-4xl sm:text-5xl font-extrabold mb-4">Welcome to TechStore</h1>
        <p class="text-lg sm:text-xl text-indigo-100 mb-8 max-w-2xl mx-auto">
          Discover the latest laptops, smartphones, tablets and accessories at the best prices.
        </p>
        <RouterLink to="/products" class="inline-block bg-white text-indigo-600 font-semibold px-8 py-3 rounded-lg hover:bg-gray-100 transition">
          Shop Now
        </RouterLink>
      </div>
    </section>

    <!-- Categories -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <h2 class="text-2xl font-bold text-gray-900 mb-6">Shop by Category</h2>
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <RouterLink
          v-for="cat in categories"
          :key="cat.id"
          :to="`/products?category=${cat.slug}`"
          class="bg-white rounded-lg p-4 text-center shadow-sm hover:shadow-md transition border border-gray-100"
        >
          <p class="font-medium text-gray-900">{{ cat.name }}</p>
          <p class="text-sm text-gray-500 mt-1">{{ cat.products_count }} items</p>
        </RouterLink>
      </div>
    </section>

    <!-- Featured Products -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Featured Products</h2>
        <RouterLink to="/products" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">
          View All &rarr;
        </RouterLink>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <RouterLink
          v-for="product in featuredProducts"
          :key="product.id"
          :to="`/products/${product.slug}`"
          class="bg-white rounded-lg shadow-sm hover:shadow-md transition overflow-hidden border border-gray-100"
        >
          <div class="h-48 bg-gray-100 flex items-center justify-center">
            <img v-if="product.image_url" :src="product.image_url" :alt="product.name" class="h-full w-full object-cover" />
            <span v-else class="text-gray-400 text-sm">No Image</span>
          </div>
          <div class="p-4">
            <p class="text-xs text-indigo-600 font-medium mb-1">{{ product.category?.name }}</p>
            <h3 class="font-semibold text-gray-900 truncate">{{ product.name }}</h3>
            <div class="flex justify-between items-center mt-2">
              <span class="text-lg font-bold text-gray-900">${{ product.price }}</span>
              <span v-if="product.stock > 0" class="text-xs text-green-600 bg-green-50 px-2 py-1 rounded">In Stock</span>
              <span v-else class="text-xs text-red-600 bg-red-50 px-2 py-1 rounded">Out of Stock</span>
            </div>
          </div>
        </RouterLink>
      </div>
    </section>
  </DefaultLayout>
</template>
