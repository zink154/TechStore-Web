<script setup>
import DefaultLayout from '@/components/layout/DefaultLayout.vue'
import { ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/lib/axios'
import { useI18n } from 'vue-i18n'
import { useMeta } from '@/composables/useMeta'

const { t } = useI18n()
useMeta({ value: t('auth.forgot_title') })

const email = ref('')
const loading = ref(false)
const sent = ref(false)
const error = ref('')

async function handleSubmit() {
  error.value = ''
  loading.value = true
  try {
    await api.post('/auth/forgot-password', { email: email.value })
    sent.value = true
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Something went wrong.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <DefaultLayout>
    <div class="min-h-[70vh] flex items-center justify-center px-4 py-12">
      <div class="w-full max-w-md">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 text-center mb-2">{{ t('auth.forgot_title') }}</h1>
        <p class="text-center text-gray-500 dark:text-gray-400 mb-8 text-sm">{{ t('auth.forgot_subtitle') }}</p>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 p-8">
          <template v-if="sent">
            <div class="text-center">
              <div class="w-16 h-16 mx-auto mb-4 bg-green-50 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>
                </svg>
              </div>
              <p class="text-gray-700 dark:text-gray-300 mb-6">{{ t('auth.reset_sent') }}</p>
              <RouterLink to="/login" class="text-indigo-600 dark:text-indigo-400 font-medium hover:text-indigo-700">
                {{ t('auth.back_to_login') }}
              </RouterLink>
            </div>
          </template>

          <form v-else @submit.prevent="handleSubmit" class="space-y-5">
            <div v-if="error" class="bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-sm p-3 rounded-lg">{{ error }}</div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('auth.email') }}</label>
              <input v-model="email" type="email" required class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none" />
            </div>

            <button type="submit" :disabled="loading" class="w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 disabled:opacity-50 cursor-pointer">
              {{ loading ? t('auth.sending') : t('auth.send_reset_link') }}
            </button>

            <p class="text-center text-sm text-gray-500 dark:text-gray-400">
              <RouterLink to="/login" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">{{ t('auth.back_to_login') }}</RouterLink>
            </p>
          </form>
        </div>
      </div>
    </div>
  </DefaultLayout>
</template>
