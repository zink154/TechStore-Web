<script setup>
import { ref, onMounted } from 'vue'
import api from '@/lib/axios'

const stats = ref(null)
const loading = ref(true)

const statusColors = {
  pending: 'bg-yellow-100 text-yellow-800',
  processing: 'bg-blue-100 text-blue-800',
  shipped: 'bg-purple-100 text-purple-800',
  delivered: 'bg-green-100 text-green-800',
  cancelled: 'bg-red-100 text-red-800',
}

onMounted(async () => {
  const { data } = await api.get('/admin/dashboard')
  stats.value = data.data
  loading.value = false
})
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Dashboard</h1>

    <div v-if="loading" class="text-gray-500">Loading...</div>

    <template v-else-if="stats">
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
          <p class="text-sm text-gray-500">Total Products</p>
          <p class="text-3xl font-bold text-gray-900 mt-1">{{ stats.total_products }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
          <p class="text-sm text-gray-500">Total Orders</p>
          <p class="text-3xl font-bold text-gray-900 mt-1">{{ stats.total_orders }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
          <p class="text-sm text-gray-500">Customers</p>
          <p class="text-3xl font-bold text-gray-900 mt-1">{{ stats.total_customers }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
          <p class="text-sm text-gray-500">Revenue</p>
          <p class="text-3xl font-bold text-green-600 mt-1">${{ Number(stats.total_revenue).toFixed(2) }}</p>
        </div>
      </div>

      <!-- Orders by Status -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Orders by Status</h2>
          <div class="space-y-3">
            <div v-for="(count, status) in stats.orders_by_status" :key="status" class="flex justify-between items-center">
              <span :class="[statusColors[status], 'px-3 py-1 rounded-full text-xs font-medium capitalize']">{{ status }}</span>
              <span class="font-semibold text-gray-900">{{ count }}</span>
            </div>
          </div>
        </div>

        <!-- Recent Orders -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Orders</h2>
          <div class="space-y-3">
            <div v-for="order in stats.recent_orders" :key="order.id" class="flex justify-between items-center text-sm">
              <div>
                <span class="font-medium text-gray-900">#{{ order.id }}</span>
                <span class="text-gray-500 ml-2">{{ order.user?.name }}</span>
              </div>
              <div class="flex items-center gap-2">
                <span :class="[statusColors[order.status], 'px-2 py-0.5 rounded-full text-xs capitalize']">{{ order.status }}</span>
                <span class="font-medium">${{ order.total }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
