<script setup>
import Navbar from './Navbar.vue'
import Footer from './Footer.vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
</script>

<template>
  <div class="min-h-screen flex flex-col bg-gray-50 dark:bg-gray-950">
    <a
      href="#main-content"
      class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-[100] focus:bg-indigo-600 focus:text-white focus:px-4 focus:py-2 focus:rounded-lg focus:text-sm focus:font-medium"
    >
      {{ t('common.skip_to_content') }}
    </a>
    <Navbar />
    <main id="main-content" class="flex-1">
      <slot />
    </main>
    <Footer />
  </div>
</template>
