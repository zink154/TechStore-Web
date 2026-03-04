<script setup>
import { ref, onMounted } from 'vue'
import api from '@/lib/axios'
import { useCurrency } from '@/composables/useCurrency'

const { formatPrice } = useCurrency()

const orders = ref([])
const loading = ref(true)
const filterStatus = ref('')

const statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled']

const statusColors = {
  pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
  processing: 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
  shipped: 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300',
  delivered: 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
  cancelled: 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
}

onMounted(() => fetchOrders())

async function fetchOrders() {
  loading.value = true
  const params = {}
  if (filterStatus.value) params.status = filterStatus.value
  const { data } = await api.get('/admin/orders', { params })
  orders.value = data.data.data
  loading.value = false
}

async function updateStatus(orderId, newStatus) {
  await api.patch(`/admin/orders/${orderId}/status`, { status: newStatus })
  await fetchOrders()
}
</script>

<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Orders</h1>
      <select v-model="filterStatus" @change="fetchOrders" class="border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm outline-none">
        <option value="">All Statuses</option>
        <option v-for="s in statuses" :key="s" :value="s" class="capitalize">{{ s }}</option>
      </select>
    </div>

    <div v-if="loading" class="text-gray-500 dark:text-gray-400">Loading...</div>

    <div v-else-if="orders.length === 0" class="text-center py-12 text-gray-500 dark:text-gray-400">No orders found.</div>

    <div v-else class="space-y-4">
      <div v-for="order in orders" :key="order.id" class="bg-white rounded-lg shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700 p-6">
        <div class="flex flex-wrap justify-between items-start gap-4 mb-4">
          <div>
            <h3 class="font-semibold text-gray-900 dark:text-gray-100">Order #{{ order.id }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
              {{ order.user?.name }} &mdash; {{ order.phone }}
            </p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ new Date(order.created_at).toLocaleString() }}</p>
          </div>
          <div class="text-right">
            <span class="font-bold text-lg">{{ formatPrice(order.total) }}</span>
            <div class="mt-2">
              <select
                :value="order.status"
                @change="updateStatus(order.id, ($event.target).value)"
                :class="[statusColors[order.status], 'rounded-full text-xs font-medium px-3 py-1 border-0 outline-none cursor-pointer']"
              >
                <option v-for="s in statuses" :key="s" :value="s" class="capitalize">{{ s }}</option>
              </select>
            </div>
          </div>
        </div>

        <div class="text-sm text-gray-600 dark:text-gray-400 mb-3">
          <strong>Address:</strong> {{ order.shipping_address }}
          <span v-if="order.notes"> | <strong>Notes:</strong> {{ order.notes }}</span>
        </div>

        <div class="border-t border-gray-100 dark:border-gray-700 pt-3 space-y-1">
          <div v-for="item in order.items" :key="item.id" class="flex justify-between text-sm">
            <span class="text-gray-600 dark:text-gray-400">{{ item.product_name }} x{{ item.quantity }}</span>
            <span>{{ formatPrice(item.price * item.quantity) }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
