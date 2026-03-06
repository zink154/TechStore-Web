<script setup>
import { ref, onMounted } from 'vue'
import api from '@/lib/axios'
import { useAuthStore } from '@/stores/auth'
import { useI18n } from 'vue-i18n'
import { useToast } from '@/composables/useToast'
import StarRating from './StarRating.vue'

const props = defineProps({
  productSlug: { type: String, required: true },
})

const { t } = useI18n()
const toast = useToast()
const auth = useAuthStore()

const reviews = ref([])
const loading = ref(true)
const submitting = ref(false)
const hasReviewed = ref(false)
const rating = ref(0)
const comment = ref('')
const error = ref('')

async function fetchReviews() {
  loading.value = true
  try {
    const { data } = await api.get(`/products/${props.productSlug}/reviews`)
    reviews.value = data.data.data
    if (auth.isAuthenticated && auth.user) {
      hasReviewed.value = reviews.value.some(r => r.user_id === auth.user.id)
    }
  } catch { /* silent */ }
  finally { loading.value = false }
}

async function submitReview() {
  if (rating.value === 0) return
  error.value = ''
  submitting.value = true
  try {
    const { data } = await api.post(`/products/${props.productSlug}/reviews`, {
      rating: rating.value,
      comment: comment.value,
    })
    reviews.value.unshift(data.data)
    hasReviewed.value = true
    rating.value = 0
    comment.value = ''
    toast.success(t('reviews.submitted'))
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Failed to submit review.'
  } finally {
    submitting.value = false
  }
}

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' })
}

onMounted(fetchReviews)
</script>

<template>
  <div class="mt-12">
    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">{{ t('reviews.title') }}</h2>

    <!-- Review Form -->
    <div v-if="auth.isAuthenticated && !hasReviewed" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700 p-6 mb-8">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ t('reviews.write_review') }}</h3>

      <form @submit.prevent="submitReview" class="space-y-4">
        <div v-if="error" class="bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-sm p-3 rounded-lg">{{ error }}</div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ t('reviews.your_rating') }}</label>
          <StarRating v-model="rating" size="lg" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('reviews.your_comment') }}</label>
          <textarea
            v-model="comment"
            required
            rows="3"
            :placeholder="t('reviews.comment_placeholder')"
            class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none text-sm"
          ></textarea>
        </div>

        <button
          type="submit"
          :disabled="submitting || rating === 0"
          class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 disabled:opacity-50 cursor-pointer"
        >
          {{ submitting ? t('reviews.submitting') : t('reviews.submit') }}
        </button>
      </form>
    </div>

    <div v-else-if="!auth.isAuthenticated" class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 mb-8 text-center">
      <p class="text-gray-500 dark:text-gray-400 text-sm">{{ t('reviews.login_to_review') }}</p>
    </div>

    <!-- Review List -->
    <div v-if="loading" class="space-y-4">
      <div v-for="n in 3" :key="n" class="animate-pulse">
        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-32 mb-2"></div>
        <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-full"></div>
      </div>
    </div>

    <div v-else-if="reviews.length" class="space-y-6">
      <div v-for="review in reviews" :key="review.id" class="border-b border-gray-100 dark:border-gray-700 pb-6 last:border-0">
        <div class="flex items-center justify-between mb-2">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900/30 rounded-full flex items-center justify-center">
              <span class="text-sm font-medium text-indigo-600 dark:text-indigo-400">{{ review.user?.name?.charAt(0)?.toUpperCase() }}</span>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ review.user?.name }}</p>
              <p class="text-xs text-gray-400 dark:text-gray-500">{{ formatDate(review.created_at) }}</p>
            </div>
          </div>
          <StarRating :value="review.rating" readonly size="sm" />
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed ml-11">{{ review.comment }}</p>
      </div>
    </div>

    <div v-else class="text-center py-8">
      <p class="text-gray-400 dark:text-gray-500">{{ t('reviews.no_reviews') }}</p>
    </div>
  </div>
</template>
