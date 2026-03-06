<script setup>
import { ref, onMounted, watch } from 'vue'
import api from '@/lib/axios'
import { useCurrency } from '@/composables/useCurrency'
import { useToast } from '@/composables/useToast'
import { useDebounce } from '@/composables/useDebounce'
import { useI18n } from 'vue-i18n'
import ConfirmModal from '@/components/common/ConfirmModal.vue'

const { t } = useI18n()
const { formatPrice } = useCurrency()
const toast = useToast()

const products = ref([])
const categories = ref([])
const loading = ref(true)
const showModal = ref(false)
const editing = ref(null)
const confirmTarget = ref(null)

const form = ref({ category_id: '', name: '', description: '', price: '', stock: '', is_active: true, image: null })

// Search & filter
const searchInput = ref('')
const debouncedSearch = useDebounce(searchInput, 400)
const filterCategory = ref('')
const filterStatus = ref('')

onMounted(async () => {
  const { data } = await api.get('/admin/categories')
  categories.value = data.data
  await fetchProducts()
})

watch(debouncedSearch, () => fetchProducts())
watch(filterCategory, () => fetchProducts())
watch(filterStatus, () => fetchProducts())

async function fetchProducts() {
  loading.value = true
  try {
    const params = {}
    if (debouncedSearch.value) params.search = debouncedSearch.value
    if (filterCategory.value) params.category_id = filterCategory.value
    if (filterStatus.value !== '') params.is_active = filterStatus.value
    const { data } = await api.get('/admin/products', { params })
    products.value = data.data.data
  } catch {
    toast.error(t('toast.load_error'))
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editing.value = null
  form.value = { category_id: '', name: '', description: '', price: '', stock: '', is_active: true, image: null }
  showModal.value = true
}

function openEdit(product) {
  editing.value = product.id
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

function onFileChange(e) {
  form.value.image = e.target.files[0] ?? null
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
    toast.success(data.data.is_featured ? 'Product featured!' : 'Product unfeatured.')
  } catch {
    toast.error('Failed to update featured status.')
  }
}

function requestDelete(id) {
  confirmTarget.value = id
}

async function confirmDelete() {
  const id = confirmTarget.value
  confirmTarget.value = null
  try {
    await api.delete(`/admin/products/${id}`)
    toast.success(t('toast.product_deleted'))
    await fetchProducts()
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Failed to delete product.')
  }
}
</script>

<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ t('admin.products') || 'Products' }}</h1>
      <button @click="openCreate" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 cursor-pointer">
        + {{ t('admin.add_product') || 'Add Product' }}
      </button>
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
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('admin.status') || 'Status' }}</label>
        <select v-model="filterStatus"
                class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm outline-none">
          <option value="">{{ t('admin.all_status') || 'All' }}</option>
          <option value="1">{{ t('admin.active') || 'Active' }}</option>
          <option value="0">{{ t('admin.inactive') || 'Inactive' }}</option>
        </select>
      </div>
    </div>

    <div v-if="loading" class="text-gray-500 dark:text-gray-400">{{ t('common.loading') }}</div>

    <div v-else class="bg-white rounded-lg shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-900 text-left">
          <tr>
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Product</th>
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Category</th>
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Price</th>
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Stock</th>
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Status</th>
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400 text-center">Featured</th>
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
          <tr v-for="product in products" :key="product.id">
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
            <td class="px-4 py-3">{{ product.stock }}</td>
            <td class="px-4 py-3">
              <span :class="product.is_active ? 'text-green-600 dark:text-green-400' : 'text-red-500 dark:text-red-400'" class="text-xs font-medium">
                {{ product.is_active ? (t('admin.active') || 'Active') : (t('admin.inactive') || 'Inactive') }}
              </span>
            </td>
            <td class="px-4 py-3 text-center">
              <button
                @click="toggleFeatured(product)"
                :aria-label="product.is_featured ? 'Remove from featured' : 'Add to featured'"
                class="cursor-pointer text-lg transition hover:scale-110"
                :class="product.is_featured ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600 hover:text-yellow-400'"
              >
                <svg xmlns="http://www.w3.org/2000/svg" :fill="product.is_featured ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 inline"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/></svg>
              </button>
            </td>
            <td class="px-4 py-3">
              <button @click="openEdit(product)" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 text-xs mr-3 cursor-pointer">{{ t('common.edit') }}</button>
              <button @click="requestDelete(product.id)" class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 text-xs cursor-pointer">{{ t('common.delete') }}</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Product Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="showModal = false" @keydown.escape="showModal = false">
      <div class="bg-white rounded-lg dark:bg-gray-800 w-full max-w-lg p-6" role="dialog" aria-modal="true">
        <h2 class="text-lg font-semibold dark:text-gray-100 mb-4">{{ editing ? 'Edit Product' : 'Add Product' }}</h2>

        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
            <select v-model="form.category_id" required class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm outline-none">
              <option value="">Select category</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
            <input v-model="form.name" required class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm outline-none" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
            <textarea v-model="form.description" rows="2" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm outline-none"></textarea>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Price ($)</label>
              <input v-model="form.price" type="number" step="0.01" min="0" required class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm outline-none" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Stock</label>
              <input v-model="form.stock" type="number" min="0" required class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm outline-none" />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Image</label>
            <input type="file" accept="image/*" @change="onFileChange" class="text-sm" />
          </div>

          <label class="flex items-center gap-2 text-sm">
            <input v-model="form.is_active" type="checkbox" />
            <span>Active</span>
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
  </div>
</template>
