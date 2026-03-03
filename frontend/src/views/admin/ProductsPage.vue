<script setup>
import { ref, onMounted } from 'vue'
import api from '@/lib/axios'
import { useCurrency } from '@/composables/useCurrency'

const { formatPrice } = useCurrency()

const products = ref([])
const categories = ref([])
const loading = ref(true)
const showModal = ref(false)
const editing = ref(null)

const form = ref({ category_id: '', name: '', description: '', price: '', stock: '', is_active: true, image: null })

onMounted(async () => {
  await fetchProducts()
  const { data } = await api.get('/admin/categories')
  categories.value = data.data
})

async function fetchProducts() {
  loading.value = true
  const { data } = await api.get('/admin/products')
  products.value = data.data.data
  loading.value = false
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

  showModal.value = false
  await fetchProducts()
}

async function deleteProduct(id) {
  if (!confirm('Delete this product?')) return
  await api.delete(`/admin/products/${id}`)
  await fetchProducts()
}
</script>

<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Products</h1>
      <button @click="openCreate" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 cursor-pointer">
        + Add Product
      </button>
    </div>

    <div v-if="loading" class="text-gray-500">Loading...</div>

    <div v-else class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left">
          <tr>
            <th class="px-4 py-3 font-medium text-gray-500">Product</th>
            <th class="px-4 py-3 font-medium text-gray-500">Category</th>
            <th class="px-4 py-3 font-medium text-gray-500">Price</th>
            <th class="px-4 py-3 font-medium text-gray-500">Stock</th>
            <th class="px-4 py-3 font-medium text-gray-500">Status</th>
            <th class="px-4 py-3 font-medium text-gray-500">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="product in products" :key="product.id">
            <td class="px-4 py-3">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gray-100 rounded flex-shrink-0 overflow-hidden">
                  <img v-if="product.image_url" :src="product.image_url" class="w-full h-full object-cover" />
                </div>
                <span class="font-medium text-gray-900">{{ product.name }}</span>
              </div>
            </td>
            <td class="px-4 py-3 text-gray-500">{{ product.category?.name }}</td>
            <td class="px-4 py-3 font-medium">{{ formatPrice(product.price) }}</td>
            <td class="px-4 py-3">{{ product.stock }}</td>
            <td class="px-4 py-3">
              <span :class="product.is_active ? 'text-green-600' : 'text-red-500'" class="text-xs font-medium">
                {{ product.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="px-4 py-3">
              <button @click="openEdit(product)" class="text-indigo-600 hover:text-indigo-800 text-xs mr-3 cursor-pointer">Edit</button>
              <button @click="deleteProduct(product.id)" class="text-red-500 hover:text-red-700 text-xs cursor-pointer">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-lg w-full max-w-lg p-6">
        <h2 class="text-lg font-semibold mb-4">{{ editing ? 'Edit Product' : 'Add Product' }}</h2>

        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
            <select v-model="form.category_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none">
              <option value="">Select category</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
            <input v-model="form.name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea v-model="form.description" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none"></textarea>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Price ($)</label>
              <input v-model="form.price" type="number" step="0.01" min="0" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Stock</label>
              <input v-model="form.stock" type="number" min="0" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none" />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Image</label>
            <input type="file" accept="image/*" @change="onFileChange" class="text-sm" />
          </div>

          <label class="flex items-center gap-2 text-sm">
            <input v-model="form.is_active" type="checkbox" />
            <span>Active</span>
          </label>

          <div class="flex gap-3 pt-2">
            <button type="button" @click="showModal = false" class="flex-1 border border-gray-300 text-gray-700 py-2 rounded-lg hover:bg-gray-50 cursor-pointer">Cancel</button>
            <button type="submit" class="flex-1 bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700 cursor-pointer">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
