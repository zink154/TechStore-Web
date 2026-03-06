<script setup>
import DefaultLayout from '@/components/layout/DefaultLayout.vue'
import { ref } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import api from '@/lib/axios'
import { useI18n } from 'vue-i18n'
import { useToast } from '@/composables/useToast'
import { useMeta } from '@/composables/useMeta'

const { t } = useI18n()
const toast = useToast()
const route = useRoute()
const router = useRouter()
useMeta({ value: t('auth.reset_title') })

const form = ref({
  token: route.query.token ?? '',
  email: route.query.email ?? '',
  password: '',
  password_confirmation: '',
})
const loading = ref(false)
const error = ref('')

async function handleSubmit() {
  error.value = ''
  loading.value = true
  try {
    await api.post('/auth/reset-password', form.value)
    toast.success(t('auth.reset_success'))
    router.push('/login')
  } catch (e) {
    error.value = e.response?.data?.message ?? t('auth.reset_failed')
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <DefaultLayout>
    <div class="min-h-[70vh] flex items-center justify-center px-4 py-12">
      <div class="w-full max-w-md">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 text-center mb-8">{{ t('auth.reset_title') }}</h1>

        <form @submit.prevent="handleSubmit" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 p-8 space-y-5">
          <div v-if="error" class="bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-sm p-3 rounded-lg">{{ error }}</div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('auth.email') }}</label>
            <input v-model="form.email" type="email" required disabled class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 outline-none bg-gray-50 dark:bg-gray-600" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('auth.new_password') }}</label>
            <input v-model="form.password" type="password" required class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('auth.confirm_new_password') }}</label>
            <input v-model="form.password_confirmation" type="password" required class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none" />
          </div>

          <button type="submit" :disabled="loading" class="w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 disabled:opacity-50 cursor-pointer">
            {{ loading ? t('auth.resetting') : t('auth.reset_btn') }}
          </button>

          <p class="text-center text-sm text-gray-500 dark:text-gray-400">
            <RouterLink to="/login" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">{{ t('auth.back_to_login') }}</RouterLink>
          </p>
        </form>
      </div>
    </div>
  </DefaultLayout>
</template>
