<script setup>
import DefaultLayout from '@/components/layout/DefaultLayout.vue'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import { RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const cart = useCartStore()
const auth = useAuthStore()
</script>

<template>
  <DefaultLayout>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-6">{{ t('cart.title') }}</h1>

      <div v-if="cart.items.length === 0" class="text-center py-16">
        <p class="text-gray-500 text-lg mb-4">{{ t('cart.empty') }}</p>
        <RouterLink to="/products" class="text-indigo-600 hover:text-indigo-700 font-medium">
          {{ t('cart.continue') }} &rarr;
        </RouterLink>
      </div>

      <template v-else>
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 divide-y divide-gray-100">
          <div v-for="item in cart.items" :key="item.id" class="flex items-center p-4 gap-4">
            <!-- Image -->
            <div class="w-20 h-20 bg-gray-100 rounded-lg flex-shrink-0 flex items-center justify-center overflow-hidden">
              <img v-if="item.image_url" :src="item.image_url" :alt="item.name" class="h-full w-full object-cover" />
              <span v-else class="text-gray-400 text-xs">{{ t('products.no_image') }}</span>
            </div>

            <!-- Info -->
            <div class="flex-1 min-w-0">
              <h3 class="font-medium text-gray-900 truncate">{{ item.name }}</h3>
              <p class="text-sm text-gray-500">${{ item.price }} {{ t('cart.each') }}</p>
            </div>

            <!-- Quantity -->
            <div class="flex items-center border border-gray-300 rounded-lg">
              <button @click="cart.updateQuantity(item.id, item.quantity - 1)" class="px-2 py-1 text-gray-600 hover:bg-gray-50 cursor-pointer">-</button>
              <span class="px-3 py-1 text-sm font-medium">{{ item.quantity }}</span>
              <button @click="cart.updateQuantity(item.id, item.quantity + 1)" class="px-2 py-1 text-gray-600 hover:bg-gray-50 cursor-pointer">+</button>
            </div>

            <!-- Subtotal -->
            <p class="font-semibold text-gray-900 w-24 text-right">${{ (item.price * item.quantity).toFixed(2) }}</p>

            <!-- Remove -->
            <button @click="cart.removeItem(item.id)" class="text-red-500 hover:text-red-700 p-1 cursor-pointer">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Summary -->
        <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-100 p-6">
          <div class="flex justify-between items-center mb-4">
            <span class="text-lg text-gray-700">{{ t('cart.total', { count: cart.count }) }}</span>
            <span class="text-2xl font-bold text-gray-900">${{ cart.total.toFixed(2) }}</span>
          </div>
          <div class="flex gap-4">
            <RouterLink to="/products" class="flex-1 text-center border border-gray-300 text-gray-700 py-3 rounded-lg font-medium hover:bg-gray-50">
              {{ t('cart.continue') }}
            </RouterLink>
            <RouterLink
              :to="auth.isAuthenticated ? '/checkout' : '/login?redirect=/checkout'"
              class="flex-1 text-center bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700"
            >
              {{ t('cart.checkout') }}
            </RouterLink>
          </div>
        </div>
      </template>
    </div>
  </DefaultLayout>
</template>
