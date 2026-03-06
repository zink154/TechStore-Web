<script setup>
const props = defineProps({
  modelValue: { type: Number, default: 0 },
  value: { type: Number, default: 0 },
  readonly: { type: Boolean, default: false },
  size: { type: String, default: 'md' },
})

const emit = defineEmits(['update:modelValue'])

const display = props.readonly ? props.value : props.modelValue

const sizeClass = {
  sm: 'w-4 h-4',
  md: 'w-5 h-5',
  lg: 'w-6 h-6',
}
</script>

<template>
  <div class="flex items-center gap-0.5">
    <button
      v-for="star in 5"
      :key="star"
      type="button"
      :disabled="readonly"
      @click="!readonly && emit('update:modelValue', star)"
      :class="[readonly ? 'cursor-default' : 'cursor-pointer hover:scale-110 transition-transform']"
      :aria-label="`${star} star${star > 1 ? 's' : ''}`"
    >
      <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        :fill="star <= (readonly ? value : modelValue) ? '#f59e0b' : 'none'"
        :stroke="star <= (readonly ? value : modelValue) ? '#f59e0b' : '#d1d5db'"
        stroke-width="1.5"
        :class="sizeClass[size] || sizeClass.md"
      >
        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/>
      </svg>
    </button>
  </div>
</template>
