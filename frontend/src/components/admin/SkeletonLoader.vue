<script setup>
defineProps({
  type: { type: String, default: 'table', validator: v => ['table', 'cards', 'chart'].includes(v) },
  rows: { type: Number, default: 5 },
  cols: { type: Number, default: 4 },
})
</script>

<template>
  <!-- Table skeleton -->
  <div v-if="type === 'table'" class="animate-pulse">
    <div class="h-10 bg-gray-200 dark:bg-gray-700 rounded mb-3"></div>
    <div v-for="r in rows" :key="r" class="flex gap-4 mb-3">
      <div v-for="c in cols" :key="c" class="h-8 bg-gray-200 dark:bg-gray-700 rounded flex-1"></div>
    </div>
  </div>

  <!-- Cards skeleton -->
  <div v-else-if="type === 'cards'" class="animate-pulse space-y-4">
    <div v-for="r in rows" :key="r" class="bg-gray-200 dark:bg-gray-700 rounded-lg h-32"></div>
  </div>

  <!-- Chart skeleton -->
  <div v-else-if="type === 'chart'" class="animate-pulse">
    <div class="h-64 bg-gray-200 dark:bg-gray-700 rounded-lg flex items-end justify-around p-6 gap-2">
      <div v-for="i in 8" :key="i" class="bg-gray-300 dark:bg-gray-600 rounded-t w-full" :style="{ height: `${20 + Math.random() * 60}%` }"></div>
    </div>
  </div>
</template>
