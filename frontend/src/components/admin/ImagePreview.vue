<script setup>
import { ref, computed, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
  currentUrl: { type: String, default: '' },
})

const emit = defineEmits(['file-selected'])

const selectedFile = ref(null)
const previewUrl = ref('')
const fileInput = ref(null)

const displayUrl = computed(() => previewUrl.value || props.currentUrl)

function onFileChange(e) {
  const file = e.target.files[0]
  if (!file) return

  if (previewUrl.value) URL.revokeObjectURL(previewUrl.value)
  selectedFile.value = file
  previewUrl.value = URL.createObjectURL(file)
  emit('file-selected', file)
}

function removeFile() {
  if (previewUrl.value) URL.revokeObjectURL(previewUrl.value)
  selectedFile.value = null
  previewUrl.value = ''
  if (fileInput.value) fileInput.value.value = ''
  emit('file-selected', null)
}

onUnmounted(() => {
  if (previewUrl.value) URL.revokeObjectURL(previewUrl.value)
})
</script>

<template>
  <div>
    <!-- Preview -->
    <div v-if="displayUrl" class="mb-3 relative inline-block">
      <img :src="displayUrl" class="h-32 w-32 object-cover rounded-lg border border-gray-200 dark:border-gray-600" />
      <button
        v-if="previewUrl"
        @click="removeFile"
        type="button"
        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600 cursor-pointer"
      >&times;</button>
      <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
        {{ previewUrl ? t('admin.image_preview') : t('admin.image_current') }}
      </p>
    </div>

    <!-- Upload -->
    <label class="block cursor-pointer">
      <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-4 text-center hover:border-indigo-400 dark:hover:border-indigo-500 transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto text-gray-400 dark:text-gray-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('admin.image_change') }}</p>
      </div>
      <input ref="fileInput" type="file" accept="image/*" @change="onFileChange" class="hidden" />
    </label>
  </div>
</template>
