<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
  currentStatus: { type: String, required: true },
})

const steps = ['pending', 'processing', 'shipped', 'delivered']

const isCancelled = computed(() => props.currentStatus === 'cancelled')
const currentIndex = computed(() => steps.indexOf(props.currentStatus))
</script>

<template>
  <div class="py-3">
    <!-- Cancelled state -->
    <div v-if="isCancelled" class="flex items-center gap-2 text-red-500">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
      </svg>
      <span class="text-sm font-medium">{{ t('orders.status.cancelled') }}</span>
    </div>

    <!-- Normal timeline -->
    <div v-else class="flex items-center">
      <template v-for="(step, i) in steps" :key="step">
        <!-- Step dot -->
        <div class="flex flex-col items-center">
          <div
            :class="[
              'w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold',
              i <= currentIndex
                ? 'bg-indigo-600 text-white'
                : 'bg-gray-200 dark:bg-gray-600 text-gray-400 dark:text-gray-500'
            ]"
          >
            <svg v-if="i < currentIndex" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>
            <span v-else>{{ i + 1 }}</span>
          </div>
          <span :class="['text-xs mt-1', i <= currentIndex ? 'text-indigo-600 dark:text-indigo-400 font-medium' : 'text-gray-400 dark:text-gray-500']">
            {{ t(`orders.status.${step}`) }}
          </span>
        </div>
        <!-- Connector line -->
        <div
          v-if="i < steps.length - 1"
          :class="['flex-1 h-0.5 mx-1 mb-5', i < currentIndex ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-600']"
        ></div>
      </template>
    </div>
  </div>
</template>
