<script setup>
import DefaultLayout from '@/components/layout/DefaultLayout.vue'
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/lib/axios'
import { useI18n } from 'vue-i18n'
import { useCurrency } from '@/composables/useCurrency'

const { t } = useI18n()
const { formatPrice } = useCurrency()
const orders = ref([])
const loading = ref(true)

const statusColors = {
  pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
  processing: 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
  shipped: 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300',
  delivered: 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
  cancelled: 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
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
      <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-6">{{ t('orders.title') }}</h1>

      <div v-if="loading" class="text-center py-12 text-gray-500 dark:text-gray-400">{{ t('common.loading') }}</div>

      <div v-else-if="orders.length === 0" class="text-center py-16">
        <p class="text-gray-500 dark:text-gray-400 text-lg mb-4">{{ t('orders.no_orders') }}</p>
        <RouterLink to="/products" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">{{ t('orders.start_shopping') }} &rarr;</RouterLink>
      </div>

      <div v-else class="space-y-4">
        <div v-for="order in orders" :key="order.id" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 p-6">
          <div class="flex flex-wrap justify-between items-start gap-4 mb-4">
            <div>
              <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ t('orders.order_id', { id: order.id }) }}</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ new Date(order.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) }}</p>
            </div>
            <div class="flex items-center gap-3">
              <span :class="[statusColors[order.status], 'px-3 py-1 rounded-full text-xs font-medium']">
                {{ t(`orders.status.${order.status}`) }}
              </span>
              <span class="font-bold text-gray-900 dark:text-gray-100">{{ formatPrice(order.total) }}</span>
            </div>
          </div>

          <div class="border-t border-gray-100 dark:border-gray-700 pt-3 space-y-2">
            <div v-for="item in order.items" :key="item.id" class="flex justify-between text-sm">
              <span class="text-gray-600 dark:text-gray-400">{{ item.product_name }} x{{ item.quantity }}</span>
              <span class="text-gray-900 dark:text-gray-100">{{ formatPrice(item.price * item.quantity) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </DefaultLayout>
</template>
