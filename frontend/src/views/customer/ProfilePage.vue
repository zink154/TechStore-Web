<script setup>
import DefaultLayout from '@/components/layout/DefaultLayout.vue'
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/lib/axios'

const auth = useAuthStore()

const form = ref({
  name: auth.user?.name ?? '',
  phone: auth.user?.phone ?? '',
  address: auth.user?.address ?? '',
})
const success = ref('')
const error = ref('')
const loading = ref(false)

async function handleSubmit() {
  success.value = ''
  error.value = ''
  loading.value = true
  try {
    const { data } = await api.put('/auth/profile', form.value)
    auth.user = data.data
    success.value = 'Profile updated successfully.'
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Update failed.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <DefaultLayout>
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-6">My Profile</h1>

      <form @submit.prevent="handleSubmit" class="bg-white rounded-lg shadow-sm border border-gray-100 p-8 space-y-5">
        <div v-if="success" class="bg-green-50 text-green-600 text-sm p-3 rounded-lg">{{ success }}</div>
        <div v-if="error" class="bg-red-50 text-red-600 text-sm p-3 rounded-lg">{{ error }}</div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input :value="auth.user?.email" disabled class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2 text-gray-500" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
          <input v-model="form.name" type="text" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
          <input v-model="form.phone" type="tel" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
          <textarea v-model="form.address" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"></textarea>
        </div>

        <button type="submit" :disabled="loading" class="w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 disabled:opacity-50 cursor-pointer">
          {{ loading ? 'Saving...' : 'Save Changes' }}
        </button>
      </form>
    </div>
  </DefaultLayout>
</template>
