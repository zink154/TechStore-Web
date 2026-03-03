<script setup>
import DefaultLayout from '@/components/layout/DefaultLayout.vue'
import { ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const router = useRouter()
const auth = useAuthStore()

const form = ref({ name: '', email: '', password: '', password_confirmation: '' })
const errors = ref({})
const loading = ref(false)

async function handleSubmit() {
  errors.value = {}
  loading.value = true
  try {
    await auth.register(form.value)
    router.push('/')
  } catch (e) {
    if (e.response?.data?.errors) {
      errors.value = e.response.data.errors
    } else {
      errors.value = { general: [e.response?.data?.message ?? 'Registration failed.'] }
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <DefaultLayout>
    <div class="min-h-[70vh] flex items-center justify-center px-4 py-12">
      <div class="w-full max-w-md">
        <h1 class="text-3xl font-bold text-gray-900 text-center mb-8">{{ t('auth.register_title') }}</h1>

        <form @submit.prevent="handleSubmit" class="bg-white rounded-lg shadow-sm border border-gray-100 p-8 space-y-5">
          <div v-if="errors.general" class="bg-red-50 text-red-600 text-sm p-3 rounded-lg">{{ errors.general[0] }}</div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('auth.name') }}</label>
            <input v-model="form.name" type="text" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none" />
            <p v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name[0] }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('auth.email') }}</label>
            <input v-model="form.email" type="email" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none" />
            <p v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email[0] }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('auth.password') }}</label>
            <input v-model="form.password" type="password" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none" />
            <p v-if="errors.password" class="text-red-500 text-xs mt-1">{{ errors.password[0] }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('auth.confirm_password') }}</label>
            <input v-model="form.password_confirmation" type="password" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none" />
          </div>

          <button type="submit" :disabled="loading" class="w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 disabled:opacity-50 cursor-pointer">
            {{ loading ? t('common.loading') : t('auth.register_btn') }}
          </button>

          <p class="text-center text-sm text-gray-500">
            {{ t('auth.have_account') }}
            <RouterLink to="/login" class="text-indigo-600 hover:text-indigo-700 font-medium">{{ t('auth.login_link') }}</RouterLink>
          </p>
        </form>
      </div>
    </div>
  </DefaultLayout>
</template>
