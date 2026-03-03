<script setup>
import DefaultLayout from '@/components/layout/DefaultLayout.vue'
</script>

<template>
  <DefaultLayout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
      <h1 class="text-6xl font-extrabold text-gray-300 mb-4">404</h1>
      <p class="text-xl text-gray-600 mb-8">Page not found</p>
      <RouterLink to="/" class="bg-indigo-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-indigo-700">
        Go Home
      </RouterLink>
    </div>
  </DefaultLayout>
</template>
