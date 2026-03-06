<script setup>
import DefaultLayout from '@/components/layout/DefaultLayout.vue'
import { ref, onMounted, computed } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import api from '@/lib/axios'
import { useI18n } from 'vue-i18n'
import { useCurrency } from '@/composables/useCurrency'
import { useMeta } from '@/composables/useMeta'

const { t } = useI18n()
const { formatPrice } = useCurrency()
const route = useRoute()
const order = ref(null)
const loading = ref(true)

useMeta(computed(() => t('order_confirmation.title')))

const statusColors = {
  pending: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
  processing: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
  shipped: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
  delivered: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
  cancelled: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
}

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' })
}

onMounted(async () => {
  try {
    const { data } = await api.get(`/orders/${route.params.id}`)
    order.value = data.data
  } catch { /* will show error state */ }
  finally { loading.value = false }
})
</script>

<template>
  <DefaultLayout>
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div v-if="loading" class="text-center py-20">
        <div class="animate-spin w-8 h-8 border-4 border-indigo-600 border-t-transparent rounded-full mx-auto"></div>
      </div>

      <template v-else-if="order">
        <!-- Success Icon -->
        <div class="text-center mb-8">
          <div class="w-20 h-20 mx-auto mb-4 bg-green-50 dark:bg-green-900/30 rounded-full flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
            </svg>
          </div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">{{ t('order_confirmation.heading') }}</h1>
          <p class="text-gray-500 dark:text-gray-400">{{ t('order_confirmation.subtitle') }}</p>
        </div>

        <!-- Order Card -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 p-6 mb-6">
          <div class="flex justify-between items-start mb-4">
            <div>
              <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ t('order_confirmation.order_id', { id: order.id }) }}</h2>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('order_confirmation.placed_on', { date: formatDate(order.created_at) }) }}</p>
            </div>
            <span :class="['px-3 py-1 rounded-full text-xs font-medium', statusColors[order.status]]">
              {{ t(`orders.status.${order.status}`) }}
            </span>
          </div>

          <!-- Items -->
          <div class="border-t border-gray-100 dark:border-gray-700 pt-4 mb-4">
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ t('order_confirmation.items') }}</h3>
            <div v-for="item in order.items" :key="item.id" class="flex justify-between text-sm py-2">
              <span class="text-gray-600 dark:text-gray-400">{{ item.product_name }} x{{ item.quantity }}</span>
              <span class="font-medium text-gray-900 dark:text-gray-100">{{ formatPrice(item.price * item.quantity) }}</span>
            </div>
          </div>

          <div class="border-t border-gray-200 dark:border-gray-600 pt-4 flex justify-between">
            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ t('order_confirmation.total') }}</span>
            <span class="font-bold text-lg text-gray-900 dark:text-gray-100">{{ formatPrice(order.total) }}</span>
          </div>

          <!-- Shipping Info -->
          <div v-if="order.shipping_address" class="border-t border-gray-100 dark:border-gray-700 pt-4 mt-4">
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('order_confirmation.shipping_to') }}</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ order.shipping_address }}</p>
            <p v-if="order.phone" class="text-sm text-gray-600 dark:text-gray-400">{{ order.phone }}</p>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-4">
          <RouterLink to="/products" class="flex-1 text-center border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 py-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 font-medium">
            {{ t('order_confirmation.continue_shopping') }}
          </RouterLink>
          <RouterLink to="/orders" class="flex-1 text-center bg-indigo-600 text-white py-3 rounded-lg hover:bg-indigo-700 font-medium">
            {{ t('order_confirmation.view_orders') }}
          </RouterLink>
        </div>
      </template>

      <div v-else class="text-center py-20">
        <p class="text-gray-500 dark:text-gray-400">{{ t('common.error') }}</p>
      </div>
    </div>
  </DefaultLayout>
</template>
