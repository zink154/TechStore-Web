<script setup>
import { ref, onMounted, watch } from 'vue'
import api from '@/lib/axios'
import { useCurrency } from '@/composables/useCurrency'
import { useToast } from '@/composables/useToast'
import { useDebounce } from '@/composables/useDebounce'
import { useI18n } from 'vue-i18n'
import { useCsvExport } from '@/composables/useCsvExport'
import { useUndoDelete } from '@/composables/useUndoDelete'
import ConfirmModal from '@/components/common/ConfirmModal.vue'
import PaginationBar from '@/components/admin/PaginationBar.vue'
import SkeletonLoader from '@/components/admin/SkeletonLoader.vue'
import SortableHeader from '@/components/admin/SortableHeader.vue'
import ImagePreview from '@/components/admin/ImagePreview.vue'

const { t } = useI18n()
const { formatPrice } = useCurrency()
const toast = useToast()
const { exportCsv } = useCsvExport()
const undoDelete = useUndoDelete()

const products = ref([])
const categories = ref([])
const loading = ref(true)
const showModal = ref(false)
const editing = ref(null)
const editingImageUrl = ref('')
const confirmTarget = ref(null)
const bulkConfirm = ref(null)

const form = ref({ category_id: '', name: '', description: '', price: '', stock: '', is_active: true, image: null })

// Search, filter, sort, pagination
const searchInput = ref('')
const debouncedSearch = useDebounce(searchInput, 400)
const filterCategory = ref('')
const filterStatus = ref('')
const sortBy = ref('created_at')
const sortDir = ref('desc')
const page = ref(1)
const pagination = ref({ current_page: 1, last_page: 1, total: 0, from: 0, to: 0 })

// Bulk selection
const selectedIds = ref(new Set())
const selectAllOnPage = ref(false)

onMounted(async () => {
  const { data } = await api.get('/admin/categories')
  categories.value = data.data
  await fetchProducts()
})

watch(debouncedSearch, () => { page.value = 1; fetchProducts() })
watch(filterCategory, () => { page.value = 1; fetchProducts() })
watch(filterStatus, () => { page.value = 1; fetchProducts() })

async function fetchProducts() {
  loading.value = true
  try {
    const params = { page: page.value, sort_by: sortBy.value, sort_dir: sortDir.value }
    if (debouncedSearch.value) params.search = debouncedSearch.value
    if (filterCategory.value) params.category_id = filterCategory.value
    if (filterStatus.value !== '') params.is_active = filterStatus.value
    const { data } = await api.get('/admin/products', { params })
    products.value = data.data.data
    pagination.value = {
      current_page: data.data.current_page,
      last_page: data.data.last_page,
      total: data.data.total,
      from: data.data.from || 0,
      to: data.data.to || 0,
    }
    selectAllOnPage.value = false
  } catch {
    toast.error(t('toast.load_error'))
  } finally {
    loading.value = false
  }
}

function handleSort(column) {
  if (sortBy.value === column) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortBy.value = column
    sortDir.value = 'asc'
  }
  page.value = 1
  fetchProducts()
}

function handlePageChange(p) {
  page.value = p
  fetchProducts()
}

// Bulk
function toggleSelectAll() {
  if (selectAllOnPage.value) {
    products.value.forEach(p => selectedIds.value.delete(p.id))
    selectAllOnPage.value = false
  } else {
    products.value.forEach(p => selectedIds.value.add(p.id))
    selectAllOnPage.value = true
  }
}

function toggleSelect(id) {
  if (selectedIds.value.has(id)) {
    selectedIds.value.delete(id)
  } else {
    selectedIds.value.add(id)
  }
}

function requestBulk(action) {
  bulkConfirm.value = action
}

async function confirmBulkAction() {
  const action = bulkConfirm.value
  bulkConfirm.value = null
  const ids = [...selectedIds.value]
  try {
    await api.post('/admin/products/bulk', { ids, action })
    toast.success(t('admin.bulk_success'))
    selectedIds.value.clear()
    selectAllOnPage.value = false
    await fetchProducts()
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Bulk action failed.')
  }
}

// CRUD
function openCreate() {
  editing.value = null
  editingImageUrl.value = ''
  form.value = { category_id: '', name: '', description: '', price: '', stock: '', is_active: true, image: null }
  showModal.value = true
}

function openEdit(product) {
  editing.value = product.id
  editingImageUrl.value = product.image_url || ''
  form.value = {
    category_id: product.category_id,
    name: product.name,
    description: product.description ?? '',
    price: product.price,
    stock: product.stock,
    is_active: product.is_active,
    image: null,
  }
  showModal.value = true
}

function onImageSelected(file) {
  form.value.image = file
}

async function handleSubmit() {
  const fd = new FormData()
  fd.append('category_id', form.value.category_id)
  fd.append('name', form.value.name)
  fd.append('description', form.value.description)
  fd.append('price', form.value.price)
  fd.append('stock', form.value.stock)
  fd.append('is_active', form.value.is_active ? '1' : '0')
  if (form.value.image) fd.append('image', form.value.image)

  try {
    if (editing.value) {
      fd.append('_method', 'PUT')
      await api.post(`/admin/products/${editing.value}`, fd, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
    } else {
      await api.post('/admin/products', fd, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
    }
    toast.success(t('toast.product_saved'))
    showModal.value = false
    await fetchProducts()
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Failed to save product.')
  }
}

async function toggleFeatured(product) {
  try {
    const { data } = await api.patch(`/admin/products/${product.id}/featured`)
    product.is_featured = data.data.is_featured
  } catch {
    toast.error(t('common.error'))
  }
}

async function duplicateProduct(product) {
  try {
    await api.post(`/admin/products/${product.id}/duplicate`)
    toast.success(t('admin.duplicate_success'))
    await fetchProducts()
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Failed to duplicate.')
  }
}

function requestDelete(product) {
  confirmTarget.value = product
}

async function confirmDelete() {
  const product = confirmTarget.value
  confirmTarget.value = null

  // Mark as pending visually
  undoDelete.scheduleDelete(
    product.id,
    async (id) => {
      try {
        await api.delete(`/admin/products/${id}`)
        await fetchProducts()
      } catch (e) {
        toast.error(e.response?.data?.message ?? 'Failed to delete.')
      }
    },
    product.name,
  )
}

function handleExportCsv() {
  const headers = ['Name', 'Category', 'Price', 'Stock', 'Status', 'Featured']
  const rows = products.value.map(p => [
    p.name,
    p.category?.name ?? '',
    p.price,
    p.stock,
    p.is_active ? 'Active' : 'Inactive',
    p.is_featured ? 'Yes' : 'No',
  ])
  exportCsv('products', headers, rows)
}
</script>

<template>
  <div>
    <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ t('admin.products') }}</h1>
      <div class="flex gap-2">
        <button @click="handleExportCsv" class="border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
          {{ t('admin.export_csv') }}
        </button>
        <button @click="openCreate" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 cursor-pointer">
          + {{ t('admin.add_product') }}
        </button>
      </div>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700 p-4 mb-4 flex flex-wrap gap-4 items-end">
      <div class="flex-1 min-w-[200px]">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('products.search') }}</label>
        <input v-model="searchInput" type="text" :placeholder="t('products.search_placeholder')"
               class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-500" />
      </div>
      <div class="min-w-[150px]">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('products.category') }}</label>
        <select v-model="filterCategory"
                class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm outline-none">
          <option value="">{{ t('products.all_categories') }}</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
        </select>
      </div>
      <div class="min-w-[130px]">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('admin.status') }}</label>
        <select v-model="filterStatus"
                class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm outline-none">
          <option value="">{{ t('admin.all_status') }}</option>
          <option value="1">{{ t('admin.active') }}</option>
          <option value="0">{{ t('admin.inactive') }}</option>
        </select>
      </div>
    </div>

    <!-- Bulk Actions Bar -->
    <div v-if="selectedIds.size > 0" class="bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-800 rounded-lg p-3 mb-4 flex items-center justify-between">
      <span class="text-sm font-medium text-indigo-700 dark:text-indigo-300">{{ t('admin.selected_count', { count: selectedIds.size }) }}</span>
      <div class="flex gap-2">
        <button @click="requestBulk('activate')" class="text-sm px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 cursor-pointer">{{ t('admin.bulk_activate') }}</button>
        <button @click="requestBulk('deactivate')" class="text-sm px-3 py-1 bg-yellow-600 text-white rounded hover:bg-yellow-700 cursor-pointer">{{ t('admin.bulk_deactivate') }}</button>
        <button @click="requestBulk('delete')" class="text-sm px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 cursor-pointer">{{ t('admin.bulk_delete') }}</button>
      </div>
    </div>

    <!-- Loading -->
    <SkeletonLoader v-if="loading" type="table" :rows="5" :cols="7" />

    <!-- Table -->
    <div v-else class="bg-white rounded-lg shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700 overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-900 text-left">
          <tr>
            <th class="px-4 py-3 w-10">
              <input type="checkbox" :checked="selectAllOnPage" @change="toggleSelectAll" class="cursor-pointer" />
            </th>
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">{{ t('admin.product') }}</th>
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">{{ t('admin.category') }}</th>
            <SortableHeader :label="t('admin.price')" column="price" :current-sort="sortBy" :current-dir="sortDir" @sort="handleSort" />
            <SortableHeader :label="t('admin.stock')" column="stock" :current-sort="sortBy" :current-dir="sortDir" @sort="handleSort" />
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">{{ t('admin.status') }}</th>
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400 text-center">{{ t('admin.featured') }}</th>
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">{{ t('admin.actions') }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
          <tr v-for="product in products" :key="product.id" :class="{ 'opacity-40': undoDelete.isPending(product.id) }">
            <td class="px-4 py-3">
              <input type="checkbox" :checked="selectedIds.has(product.id)" @change="toggleSelect(product.id)" class="cursor-pointer" />
            </td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded flex-shrink-0 overflow-hidden">
                  <img v-if="product.image_url" :src="product.image_url" :alt="product.name" loading="lazy" class="w-full h-full object-cover" />
                </div>
                <span class="font-medium text-gray-900 dark:text-gray-100">{{ product.name }}</span>
              </div>
            </td>
            <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ product.category?.name }}</td>
            <td class="px-4 py-3 font-medium">{{ formatPrice(product.price) }}</td>
            <td class="px-4 py-3">
              <span :class="product.stock < 10 ? 'text-red-600 dark:text-red-400 font-bold' : ''">{{ product.stock }}</span>
            </td>
            <td class="px-4 py-3">
              <span :class="product.is_active ? 'text-green-600 dark:text-green-400' : 'text-red-500 dark:text-red-400'" class="text-xs font-medium">
                {{ product.is_active ? t('admin.active') : t('admin.inactive') }}
              </span>
            </td>
            <td class="px-4 py-3 text-center">
              <button @click="toggleFeatured(product)" class="cursor-pointer text-lg transition hover:scale-110"
                      :class="product.is_featured ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600 hover:text-yellow-400'">
                <svg xmlns="http://www.w3.org/2000/svg" :fill="product.is_featured ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 inline"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/></svg>
              </button>
            </td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <button @click="openEdit(product)" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 text-xs cursor-pointer">{{ t('common.edit') }}</button>
                <button @click="duplicateProduct(product)" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 text-xs cursor-pointer">{{ t('admin.duplicate') }}</button>
                <button @click="requestDelete(product)" class="text-red-500 hover:text-red-700 dark:text-red-400 text-xs cursor-pointer">{{ t('common.delete') }}</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <PaginationBar
        :current-page="pagination.current_page"
        :last-page="pagination.last_page"
        :total="pagination.total"
        :from="pagination.from"
        :to="pagination.to"
        @page-change="handlePageChange"
        class="px-4 pb-4"
      />
    </div>

    <!-- Product Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="showModal = false" @keydown.escape="showModal = false">
      <div class="bg-white rounded-lg dark:bg-gray-800 w-full max-w-lg max-h-[90vh] overflow-y-auto p-6" role="dialog" aria-modal="true">
        <h2 class="text-lg font-semibold dark:text-gray-100 mb-4">{{ editing ? t('admin.edit_product') : t('admin.add_product') }}</h2>

        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('admin.category') }}</label>
            <select v-model="form.category_id" required class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm outline-none">
              <option value="">{{ t('admin.select_category') }}</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('admin.product_name') }}</label>
            <input v-model="form.name" required class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm outline-none" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('admin.description') }}</label>
            <textarea v-model="form.description" rows="2" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm outline-none"></textarea>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('admin.price') }} ($)</label>
              <input v-model="form.price" type="number" step="0.01" min="0" required class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm outline-none" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('admin.stock') }}</label>
              <input v-model="form.stock" type="number" min="0" required class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm outline-none" />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('admin.image') }}</label>
            <ImagePreview :current-url="editingImageUrl" @file-selected="onImageSelected" />
          </div>

          <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
            <input v-model="form.is_active" type="checkbox" />
            <span>{{ t('admin.active') }}</span>
          </label>

          <div class="flex gap-3 pt-2">
            <button type="button" @click="showModal = false" class="flex-1 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 py-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">{{ t('common.cancel') }}</button>
            <button type="submit" class="flex-1 bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700 cursor-pointer">{{ t('common.save') }}</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirm -->
    <ConfirmModal
      v-if="confirmTarget"
      :title="t('confirm.delete_title')"
      :message="t('confirm.delete_product')"
      @confirm="confirmDelete"
      @cancel="confirmTarget = null"
    />

    <!-- Bulk Confirm -->
    <ConfirmModal
      v-if="bulkConfirm"
      :title="t('admin.bulk_actions')"
      :message="t('admin.bulk_confirm', { action: bulkConfirm, count: selectedIds.size })"
      :type="bulkConfirm === 'delete' ? 'danger' : 'warning'"
      @confirm="confirmBulkAction"
      @cancel="bulkConfirm = null"
    />
  </div>
</template>
