<script setup>
import DefaultLayout from '@/components/layout/DefaultLayout.vue'
import { ref, onMounted } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import api from '@/lib/axios'
import { useCartStore } from '@/stores/cart'

const route = useRoute()
const cart = useCartStore()
const product = ref(null)
const quantity = ref(1)
const loading = ref(true)
const added = ref(false)

onMounted(async () => {
  const { data } = await api.get(`/products/${route.params.id}`)
  product.value = data.data
  loading.value = false
})

function addToCart() {
  if (!product.value) return
  cart.addItem(
    { id: product.value.id, name: product.value.name, price: Number(product.value.price), image_url: product.value.image_url },
    quantity.value
  )
  added.value = true
  setTimeout(() => { added.value = false }, 2000)
}
</script>

<template>
  <DefaultLayout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div v-if="loading" class="text-center py-20 text-gray-500">Loading...</div>

      <template v-else-if="product">
        <!-- Breadcrumb -->
        <nav class="mb-6 text-sm text-gray-500">
          <RouterLink to="/products" class="hover:text-indigo-600">Products</RouterLink>
          <span class="mx-2">/</span>
          <span class="text-gray-900">{{ product.name }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
          <!-- Image -->
          <div class="bg-white rounded-xl border border-gray-100 h-96 flex items-center justify-center overflow-hidden shadow-sm">
            <img v-if="product.image_url" :src="product.image_url" :alt="product.name" class="h-full w-full object-cover hover:scale-105 transition-transform duration-300" />
            <span v-else class="text-gray-400">No Image</span>
          </div>

          <!-- Info -->
          <div>
            <p class="text-sm text-indigo-600 font-medium mb-2">{{ product.category?.name }}</p>
            <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ product.name }}</h1>
            <p class="text-3xl font-extrabold text-gray-900 mb-4">${{ product.price }}</p>

            <div class="mb-6">
              <span v-if="product.stock > 0" class="text-green-600 bg-green-50 px-3 py-1 rounded-full text-sm font-medium">
                In Stock ({{ product.stock }} available)
              </span>
              <span v-else class="text-red-600 bg-red-50 px-3 py-1 rounded-full text-sm font-medium">Out of Stock</span>
            </div>

            <p class="text-gray-600 mb-6 leading-relaxed">{{ product.description }}</p>

            <div v-if="product.stock > 0" class="flex items-center gap-4">
              <div class="flex items-center border border-gray-300 rounded-lg">
                <button @click="quantity > 1 && quantity--" class="px-3 py-2 text-gray-600 hover:bg-gray-50 cursor-pointer">-</button>
                <span class="px-4 py-2 font-medium">{{ quantity }}</span>
                <button @click="quantity < product.stock && quantity++" class="px-3 py-2 text-gray-600 hover:bg-gray-50 cursor-pointer">+</button>
              </div>
              <button
                @click="addToCart"
                class="flex-1 bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 transition cursor-pointer"
              >
                {{ added ? 'Added!' : 'Add to Cart' }}
              </button>
            </div>
          </div>
        </div>
      </template>
    </div>
  </DefaultLayout>
</template>
