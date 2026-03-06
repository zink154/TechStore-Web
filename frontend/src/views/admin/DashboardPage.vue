<script setup>
import SkeletonLoader from '@/components/admin/SkeletonLoader.vue'
import { ref, onMounted, computed, watch, nextTick } from 'vue'
import { Chart, CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend, Filler } from 'chart.js'
import api from '@/lib/axios'
import { useCurrency } from '@/composables/useCurrency'
import { useToast } from '@/composables/useToast'
import { useI18n } from 'vue-i18n'

Chart.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend, Filler)

const { t } = useI18n()
const { formatPrice } = useCurrency()
const toast = useToast()

const stats = ref(null)
const loading = ref(true)
const revenueCanvas = ref(null)
const ordersCanvas = ref(null)
let revenueChart = null
let ordersChart = null

const isDark = computed(() => document.documentElement.classList.contains('dark'))

const statusColors = {
  pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
  processing: 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
  shipped: 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300',
  delivered: 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
  cancelled: 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
}

const stockColor = (stock) => stock <= 2 ? 'text-red-600 dark:text-red-400' : 'text-yellow-600 dark:text-yellow-400'

const activityIcons = {
  created: 'text-green-500',
  updated: 'text-blue-500',
  deleted: 'text-red-500',
  bulk: 'text-purple-500',
}

function relativeTime(dateStr) {
  const diff = Date.now() - new Date(dateStr).getTime()
  const mins = Math.floor(diff / 60000)
  if (mins < 1) return 'just now'
  if (mins < 60) return `${mins}m ago`
  const hours = Math.floor(mins / 60)
  if (hours < 24) return `${hours}h ago`
  const days = Math.floor(hours / 24)
  return `${days}d ago`
}

function buildCharts() {
  if (!stats.value) return
  const gridColor = isDark.value ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.06)'
  const textColor = isDark.value ? '#9ca3af' : '#6b7280'

  const chartOptions = (yCallback) => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { display: false }, ticks: { color: textColor, maxTicksLimit: 8 } },
      y: { grid: { color: gridColor }, ticks: { color: textColor, callback: yCallback } },
    },
  })

  // Revenue chart
  if (revenueCanvas.value) {
    if (revenueChart) revenueChart.destroy()
    const labels = stats.value.revenue_trend.map(d => new Date(d.date).toLocaleDateString('en', { month: 'short', day: 'numeric' }))
    revenueChart = new Chart(revenueCanvas.value, {
      type: 'line',
      data: {
        labels,
        datasets: [{
          data: stats.value.revenue_trend.map(d => Number(d.revenue)),
          borderColor: '#6366f1',
          backgroundColor: 'rgba(99,102,241,0.1)',
          fill: true,
          tension: 0.3,
          pointRadius: 2,
        }],
      },
      options: chartOptions((v) => '$' + v),
    })
  }

  // Orders chart
  if (ordersCanvas.value) {
    if (ordersChart) ordersChart.destroy()
    const labels = stats.value.orders_trend.map(d => new Date(d.date).toLocaleDateString('en', { month: 'short', day: 'numeric' }))
    ordersChart = new Chart(ordersCanvas.value, {
      type: 'bar',
      data: {
        labels,
        datasets: [{
          data: stats.value.orders_trend.map(d => d.count),
          backgroundColor: isDark.value ? 'rgba(99,102,241,0.6)' : 'rgba(99,102,241,0.7)',
          borderRadius: 4,
        }],
      },
      options: chartOptions(),
    })
  }
}

async function loadDashboard() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/dashboard')
    stats.value = data.data
    await nextTick()
    buildCharts()
  } catch {
    toast.error(t('toast.load_error'))
  } finally {
    loading.value = false
  }
}

onMounted(loadDashboard)
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">{{ t('admin.dashboard') }}</h1>

    <!-- Skeleton -->
    <div v-if="loading" class="space-y-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div v-for="i in 4" :key="i" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700 p-6 animate-pulse">
          <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-24 mb-3"></div>
          <div class="h-8 bg-gray-200 dark:bg-gray-700 rounded w-20"></div>
        </div>
      </div>
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <SkeletonLoader type="chart" />
        <SkeletonLoader type="chart" />
      </div>
    </div>

    <template v-else-if="stats">
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700 p-6">
          <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('admin.total_products') }}</p>
          <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ stats.total_products }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700 p-6">
          <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('admin.total_orders') }}</p>
          <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ stats.total_orders }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700 p-6">
          <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('admin.customers') }}</p>
          <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ stats.total_customers }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700 p-6">
          <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('admin.revenue') }}</p>
          <p class="text-3xl font-bold text-green-600 dark:text-green-400 mt-1">{{ formatPrice(stats.total_revenue) }}</p>
        </div>
      </div>

      <!-- Charts -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700 p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ t('admin.revenue_trend') }}</h2>
          <div class="h-64"><canvas ref="revenueCanvas"></canvas></div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700 p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ t('admin.orders_trend') }}</h2>
          <div class="h-64"><canvas ref="ordersCanvas"></canvas></div>
        </div>
      </div>

      <!-- Orders by Status + Recent Orders -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700 p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ t('admin.orders_by_status') }}</h2>
          <div class="space-y-3">
            <div v-for="(count, status) in stats.orders_by_status" :key="status" class="flex justify-between items-center">
              <span :class="[statusColors[status], 'px-3 py-1 rounded-full text-xs font-medium']">{{ t(`orders.status.${status}`) }}</span>
              <span class="font-semibold text-gray-900 dark:text-gray-100">{{ count }}</span>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700 p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ t('admin.recent_orders') }}</h2>
          <div class="space-y-3">
            <div v-for="order in stats.recent_orders" :key="order.id" class="flex justify-between items-center text-sm">
              <div>
                <span class="font-medium text-gray-900 dark:text-gray-100">#{{ order.id }}</span>
                <span class="text-gray-500 dark:text-gray-400 ml-2">{{ order.user?.name }}</span>
              </div>
              <div class="flex items-center gap-2">
                <span :class="[statusColors[order.status], 'px-2 py-0.5 rounded-full text-xs']">{{ t(`orders.status.${order.status}`) }}</span>
                <span class="font-medium">{{ formatPrice(order.total) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Low Stock + Activity Log -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Low Stock Alert -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700 p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
            <span class="inline-flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
              {{ t('admin.low_stock') }}
            </span>
          </h2>
          <div v-if="stats.low_stock_products?.length" class="space-y-2">
            <div v-for="p in stats.low_stock_products" :key="p.id" class="flex items-center justify-between text-sm">
              <div class="flex items-center gap-3">
                <img v-if="p.image_url" :src="p.image_url" class="w-8 h-8 rounded object-cover" />
                <div v-else class="w-8 h-8 rounded bg-gray-200 dark:bg-gray-600"></div>
                <span class="text-gray-900 dark:text-gray-100">{{ p.name }}</span>
              </div>
              <span :class="[stockColor(p.stock), 'font-bold']">{{ p.stock }} left</span>
            </div>
          </div>
          <p v-else class="text-sm text-gray-400 dark:text-gray-500">{{ t('admin.low_stock_empty') }}</p>
        </div>

        <!-- Activity Log -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700 p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ t('admin.activity_log') }}</h2>
          <div v-if="stats.activity_logs?.length" class="space-y-3">
            <div v-for="log in stats.activity_logs" :key="log.id" class="flex items-start gap-3 text-sm">
              <div :class="[activityIcons[log.action] || 'text-gray-400', 'mt-0.5']">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                  <circle cx="10" cy="10" r="4" />
                </svg>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-gray-900 dark:text-gray-100 truncate">{{ log.description }}</p>
                <p class="text-xs text-gray-400 dark:text-gray-500">{{ log.user?.name }} &middot; {{ relativeTime(log.created_at) }}</p>
              </div>
            </div>
          </div>
          <p v-else class="text-sm text-gray-400 dark:text-gray-500">{{ t('admin.activity_empty') }}</p>
        </div>
      </div>
    </template>
  </div>
</template>
