<script setup>
import { ref, onMounted } from 'vue'
import api from '@/lib/axios'

const categories = ref([])
const loading = ref(true)
const showModal = ref(false)
const editing = ref(null)
const form = ref({ name: '' })
const error = ref('')

onMounted(() => fetchCategories())

async function fetchCategories() {
  loading.value = true
  const { data } = await api.get('/admin/categories')
  categories.value = data.data
  loading.value = false
}

function openCreate() {
  editing.value = null
  form.value = { name: '' }
  error.value = ''
  showModal.value = true
}

function openEdit(cat) {
  editing.value = cat.id
  form.value = { name: cat.name }
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
    showModal.value = false
    await fetchCategories()
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Failed.'
  }
}

async function deleteCategory(id) {
  if (!confirm('Delete this category?')) return
  try {
    await api.delete(`/admin/categories/${id}`)
    await fetchCategories()
  } catch (e) {
    alert(e.response?.data?.message ?? 'Cannot delete.')
  }
}
</script>

<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Categories</h1>
      <button @click="openCreate" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 cursor-pointer">
        + Add Category
      </button>
    </div>

    <div v-if="loading" class="text-gray-500 dark:text-gray-400">Loading...</div>

    <div v-else class="bg-white rounded-lg shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-900 text-left">
          <tr>
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Name</th>
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Slug</th>
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Products</th>
            <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
          <tr v-for="cat in categories" :key="cat.id">
            <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ cat.name }}</td>
            <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ cat.slug }}</td>
            <td class="px-4 py-3">{{ cat.products_count }}</td>
            <td class="px-4 py-3">
              <button @click="openEdit(cat)" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 text-xs mr-3 cursor-pointer">Edit</button>
              <button @click="deleteCategory(cat.id)" class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 text-xs cursor-pointer">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-lg dark:bg-gray-800 w-full max-w-sm p-6">
        <h2 class="text-lg font-semibold dark:text-gray-100 mb-4">{{ editing ? 'Edit Category' : 'Add Category' }}</h2>

        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div v-if="error" class="bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-sm p-3 rounded-lg">{{ error }}</div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
            <input v-model="form.name" required class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm outline-none" />
          </div>

          <div class="flex gap-3">
            <button type="button" @click="showModal = false" class="flex-1 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 py-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">Cancel</button>
            <button type="submit" class="flex-1 bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700 cursor-pointer">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
