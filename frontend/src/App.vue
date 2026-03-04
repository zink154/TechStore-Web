<script setup>
import { RouterView } from 'vue-router'
import { useThemeStore } from '@/stores/theme'
import AppToast from '@/components/AppToast.vue'

useThemeStore()
</script>

<template>
  <RouterView v-slot="{ Component }">
    <Transition name="page" mode="out-in">
      <component :is="Component" />
    </Transition>
  </RouterView>
  <AppToast />
</template>
