<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
  currentPage: { type: Number, required: true },
  lastPage: { type: Number, required: true },
  total: { type: Number, required: true },
  from: { type: Number, default: 0 },
  to: { type: Number, default: 0 },
})

const emit = defineEmits(['page-change'])

const pages = computed(() => {
  const items = []
  const current = props.currentPage
  const last = props.lastPage

  if (last <= 7) {
    for (let i = 1; i <= last; i++) items.push(i)
  } else {
    items.push(1)
    if (current > 3) items.push('...')
    for (let i = Math.max(2, current - 1); i <= Math.min(last - 1, current + 1); i++) {
      items.push(i)
    }
    if (current < last - 2) items.push('...')
    items.push(last)
  }
  return items
})
</script>

<template>
  <div v-if="lastPage > 1" class="flex items-center justify-between border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
    <p class="text-sm text-gray-500 dark:text-gray-400">
      {{ t('admin.showing', { from, to, total }) }}
    </p>
    <nav class="flex items-center gap-1">
      <button
        :disabled="currentPage <= 1"
        @click="emit('page-change', currentPage - 1)"
        class="px-3 py-1.5 text-sm rounded-md border border-gray-300 dark:border-gray-600 disabled:opacity-40 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer disabled:cursor-default"
      >{{ t('admin.previous') }}</button>

      <template v-for="(page, i) in pages" :key="i">
        <span v-if="page === '...'" class="px-2 text-gray-400">...</span>
        <button
          v-else
          @click="emit('page-change', page)"
          :class="[
            'px-3 py-1.5 text-sm rounded-md cursor-pointer',
            page === currentPage
              ? 'bg-indigo-600 text-white'
              : 'border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'
          ]"
        >{{ page }}</button>
      </template>

      <button
        :disabled="currentPage >= lastPage"
        @click="emit('page-change', currentPage + 1)"
        class="px-3 py-1.5 text-sm rounded-md border border-gray-300 dark:border-gray-600 disabled:opacity-40 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer disabled:cursor-default"
      >{{ t('admin.next') }}</button>
    </nav>
  </div>
</template>
