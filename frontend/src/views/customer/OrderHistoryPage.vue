<script setup>
import DefaultLayout from '@/components/layout/DefaultLayout.vue'
import { ref, onMounted } from 'vue'
import api from '@/lib/axios'

const orders = ref([])
const loading = ref(true)

const statusColors = {
  pending: 'bg-yellow-100 text-yellow-800',
  processing: 'bg-blue-100 text-blue-800',
  shipped: 'bg-purple-100 text-purple-800',
  delivered: 'bg-green-100 text-green-800',
  cancelled: 'bg-red-100 text-red-800',
}

onMounted(async () => {
  const { data } = await api.get('/orders')
  orders.value = data.data.data
  loading.value = false
})
</script>

<template>
  <DefaultLayout>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-6">My Orders</h1>

      <div v-if="loading" class="text-center py-12 text-gray-500">Loading...</div>

      <div v-else-if="orders.length === 0" class="text-center py-16">
        <p class="text-gray-500 text-lg mb-4">No orders yet</p>
        <RouterLink to="/products" class="text-indigo-600 hover:text-indigo-700 font-medium">Start Shopping &rarr;</RouterLink>
      </div>

      <div v-else class="space-y-4">
        <div v-for="order in orders" :key="order.id" class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
          <div class="flex flex-wrap justify-between items-start gap-4 mb-4">
            <div>
              <h3 class="font-semibold text-gray-900">Order #{{ order.id }}</h3>
              <p class="text-sm text-gray-500">{{ new Date(order.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) }}</p>
            </div>
            <div class="flex items-center gap-3">
              <span :class="[statusColors[order.status], 'px-3 py-1 rounded-full text-xs font-medium capitalize']">
                {{ order.status }}
              </span>
              <span class="font-bold text-gray-900">${{ order.total }}</span>
            </div>
          </div>

          <div class="border-t border-gray-100 pt-3 space-y-2">
            <div v-for="item in order.items" :key="item.id" class="flex justify-between text-sm">
              <span class="text-gray-600">{{ item.product_name }} x{{ item.quantity }}</span>
              <span class="text-gray-900">${{ (item.price * item.quantity).toFixed(2) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </DefaultLayout>
</template>
