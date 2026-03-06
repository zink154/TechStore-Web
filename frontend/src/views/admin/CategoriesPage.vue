<script setup>
import { ref, onMounted } from 'vue'
import api from '@/lib/axios'
import { useToast } from '@/composables/useToast'
import { useI18n } from 'vue-i18n'
import ConfirmModal from '@/components/common/ConfirmModal.vue'
import SkeletonLoader from '@/components/admin/SkeletonLoader.vue'

const { t } = useI18n()
const toast = useToast()
const categories = ref([])
const loading = ref(true)
const showModal = ref(false)
const editing = ref(null)
const form = ref({ name: '', description: '' })
const error = ref('')
const confirmTarget = ref(null)

onMounted(() => fetchCategories())

async function fetchCategories() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/categories')
    categories.value = data.data
  } catch {
    toast.error(t('toast.load_error'))
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editing.value = null
  form.value = { name: '', description: '' }
  error.value = ''
  showModal.value = true
}

function openEdit(cat) {
  editing.value = cat.id
  form.value = { name: cat.name, description: cat.description ?? '' }
  error.value = ''
  showModal.value = true
}

async function handleSubmit() {
  error.value = ''
  try {
    if (editing.value) {
      await api.put(`/admin/categories/${editing.value}`, form.value)
    } else {
      await api.post('/admin/categories', form.value)
    }
    toast.success(t('toast.category_saved'))
    showModal.value = false
    await fetchCategories()
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Failed.'
  }
}

function requestDelete(id) {
  confirmTarget.value = id
}

async function confirmDelete() {
  const id = confirmTarget.value
  confirmTarget.value = null
  try {
    await api.delete(`/admin/categories/${id}`)
    toast.success(t('toast.category_deleted'))
    await fetchCategories()
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Cannot delete.')
  }
}
</script>

<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ t('admin.categories') }}</h1>
      <button @click="openCreate" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 cursor-pointer">
        + {{ t('admin.add_category') }}
      </button>
    </div>

    <SkeletonLoader v-if="loading" type="table" :rows="4" :cols="5" />

    <div v-else class="bg-white rounded-lg shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-900 text-left">
          <tr>
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">{{ t('products.name') }}</th>
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Slug</th>
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">{{ t('admin.description') }}</th>
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">{{ t('admin.products') }}</th>
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">{{ t('admin.actions') }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
          <tr v-for="cat in categories" :key="cat.id">
            <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ cat.name }}</td>
            <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ cat.slug }}</td>
            <td class="px-4 py-3 text-gray-500 dark:text-gray-400 max-w-[200px] truncate">{{ cat.description || '—' }}</td>
            <td class="px-4 py-3">{{ cat.products_count }}</td>
            <td class="px-4 py-3">
              <button @click="openEdit(cat)" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 text-xs mr-3 cursor-pointer">{{ t('common.edit') }}</button>
              <button @click="requestDelete(cat.id)" class="text-red-500 hover:text-red-700 dark:text-red-400 text-xs cursor-pointer">{{ t('common.delete') }}</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="showModal = false" @keydown.escape="showModal = false">
      <div class="bg-white rounded-lg dark:bg-gray-800 w-full max-w-md p-6" role="dialog" aria-modal="true">
        <h2 class="text-lg font-semibold dark:text-gray-100 mb-4">{{ editing ? t('admin.edit_category') : t('admin.add_category') }}</h2>

        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div v-if="error" class="bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-sm p-3 rounded-lg">{{ error }}</div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('products.name') }}</label>
            <input v-model="form.name" required class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm outline-none" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('admin.description') }}</label>
            <textarea v-model="form.description" rows="3" :placeholder="t('admin.description_placeholder')"
                      class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm outline-none"></textarea>
          </div>

          <div class="flex gap-3">
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
      :message="t('confirm.delete_category')"
      @confirm="confirmDelete"
      @cancel="confirmTarget = null"
    />
  </div>
</template>
