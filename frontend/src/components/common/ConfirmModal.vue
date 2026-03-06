<script setup>
import { onMounted, onUnmounted, ref, nextTick } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
  title: { type: String, default: '' },
  message: { type: String, default: '' },
  confirmText: { type: String, default: '' },
  cancelText: { type: String, default: '' },
  type: { type: String, default: 'danger' },
})

const emit = defineEmits(['confirm', 'cancel'])
const cancelBtn = ref(null)

onMounted(() => { nextTick(() => cancelBtn.value?.focus()) })

function onKeydown(e) {
  if (e.key === 'Escape') emit('cancel')
}
onMounted(() => document.addEventListener('keydown', onKeydown))
onUnmounted(() => document.removeEventListener('keydown', onKeydown))

const btnClass = {
  danger: 'bg-red-600 hover:bg-red-700 text-white',
  warning: 'bg-yellow-600 hover:bg-yellow-700 text-white',
  info: 'bg-blue-600 hover:bg-blue-700 text-white',
}
</script>

<template>
  <Teleport to="body">
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="emit('cancel')" role="dialog" aria-modal="true">
      <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-sm p-6 shadow-xl">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
          {{ title || t('confirm.title') }}
        </h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
          {{ message || t('confirm.message') }}
        </p>
        <div class="flex gap-3">
          <button
            ref="cancelBtn"
            type="button"
            @click="emit('cancel')"
            class="flex-1 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 py-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer text-sm font-medium"
          >
            {{ cancelText || t('common.cancel') }}
          </button>
          <button
            type="button"
            @click="emit('confirm')"
            :class="['flex-1 py-2 rounded-lg font-medium cursor-pointer text-sm', btnClass[type] || btnClass.danger]"
          >
            {{ confirmText || t('common.delete') }}
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>
