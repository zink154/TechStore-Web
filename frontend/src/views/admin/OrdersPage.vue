<script setup>
import { ref, onMounted, watch } from 'vue'
import api from '@/lib/axios'
import { useCurrency } from '@/composables/useCurrency'
import { useToast } from '@/composables/useToast'
import { useDebounce } from '@/composables/useDebounce'
import { useCsvExport } from '@/composables/useCsvExport'
import { useI18n } from 'vue-i18n'
import SkeletonLoader from '@/components/admin/SkeletonLoader.vue'
import PaginationBar from '@/components/admin/PaginationBar.vue'
import OrderTimeline from '@/components/admin/OrderTimeline.vue'

const { t } = useI18n()
const { formatPrice } = useCurrency()
const toast = useToast()
const { exportCsv } = useCsvExport()

const orders = ref([])
const loading = ref(true)
const filterStatus = ref('')
const searchInput = ref('')
const debouncedSearch = useDebounce(searchInput, 400)
const page = ref(1)
const pagination = ref({ current_page: 1, last_page: 1, total: 0, from: 0, to: 0 })

const statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled']

const statusColors = {
  pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
  processing: 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
  shipped: 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300',
  delivered: 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
  cancelled: 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
}

onMounted(() => fetchOrders())

watch(filterStatus, () => { page.value = 1; fetchOrders() })
watch(debouncedSearch, () => { page.value = 1; fetchOrders() })

async function fetchOrders() {
  loading.value = true
  try {
    const params = { page: page.value }
    if (filterStatus.value) params.status = filterStatus.value
    if (debouncedSearch.value) params.search = debouncedSearch.value
    const { data } = await api.get('/admin/orders', { params })
    orders.value = data.data.data
    pagination.value = {
      current_page: data.data.current_page,
      last_page: data.data.last_page,
      total: data.data.total,
      from: data.data.from || 0,
      to: data.data.to || 0,
    }
  } catch {
    toast.error(t('toast.load_error'))
  } finally {
    loading.value = false
  }
}

function handlePageChange(p) {
  page.value = p
  fetchOrders()
}

async function updateStatus(orderId, newStatus) {
  try {
    await api.patch(`/admin/orders/${orderId}/status`, { status: newStatus })
    toast.success(t('toast.status_updated'))
    await fetchOrders()
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Failed to update status.')
  }
}

function handleExportCsv() {
  const headers = ['Order ID', 'Customer', 'Phone', 'Total', 'Status', 'Date']
  const rows = orders.value.map(o => [
    o.id,
    o.user?.name ?? '',
    o.phone ?? '',
    o.total,
    o.status,
    new Date(o.created_at).toLocaleDateString(),
  ])
  exportCsv('orders', headers, rows)
}
</script>

<template>
  <div>
    <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ t('admin.orders') }}</h1>
      <button @click="handleExportCsv" class="border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
        {{ t('admin.export_csv') }}
      </button>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700 p-4 mb-4 flex flex-wrap gap-4 items-end">
      <div class="flex-1 min-w-[200px]">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('products.search') }}</label>
        <input v-model="searchInput" type="text" :placeholder="t('admin.search_orders')"
               class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-500" />
      </div>
      <div class="min-w-[150px]">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('admin.status') }}</label>
        <select v-model="filterStatus"
                class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm outline-none">
          <option value="">{{ t('admin.all_status') }}</option>
          <option v-for="s in statuses" :key="s" :value="s">{{ t(`orders.status.${s}`) }}</option>
        </select>
      </div>
    </div>

    <!-- Loading -->
    <SkeletonLoader v-if="loading" type="cards" :rows="3" />

    <div v-else-if="orders.length === 0" class="text-center py-12 text-gray-500 dark:text-gray-400">{{ t('admin.no_data') }}</div>

    <div v-else class="space-y-4">
      <div v-for="order in orders" :key="order.id" class="bg-white rounded-lg shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700 p-6">
        <div class="flex flex-wrap justify-between items-start gap-4 mb-2">
          <div>
            <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ t('orders.order_id', { id: order.id }) }}</h3>
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
                <option v-for="s in statuses" :key="s" :value="s">{{ t(`orders.status.${s}`) }}</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Order Timeline -->
        <OrderTimeline :current-status="order.status" />

        <div class="text-sm text-gray-600 dark:text-gray-400 mb-3">
          <strong>{{ t('order_confirmation.shipping_to') }}:</strong> {{ order.shipping_address }}
          <span v-if="order.notes"> | <strong>{{ t('checkout.notes') }}:</strong> {{ order.notes }}</span>
        </div>

        <div class="border-t border-gray-100 dark:border-gray-700 pt-3 space-y-1">
          <div v-for="item in order.items" :key="item.id" class="flex justify-between text-sm">
            <span class="text-gray-600 dark:text-gray-400">{{ item.product_name }} x{{ item.quantity }}</span>
            <span>{{ formatPrice(item.price * item.quantity) }}</span>
          </div>
        </div>
      </div>

      <PaginationBar
        :current-page="pagination.current_page"
        :last-page="pagination.last_page"
        :total="pagination.total"
        :from="pagination.from"
        :to="pagination.to"
        @page-change="handlePageChange"
      />
    </div>
  </div>
</template>
