<script setup>
import { RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useCartStore } from '@/stores/cart'
import { useWishlistStore } from '@/stores/wishlist'
import { useThemeStore } from '@/stores/theme'
import { ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useToast } from '@/composables/useToast'

const { t, locale } = useI18n()
const toast = useToast()
const auth = useAuthStore()
const cart = useCartStore()
const wishlist = useWishlistStore()
const themeStore = useThemeStore()
const mobileOpen = ref(false)
const cartAnimating = ref(false)

function toggleLocale() {
  const next = locale.value === 'en' ? 'vi' : 'en'
  locale.value = next
  localStorage.setItem('locale', next)
}

watch(() => cart.count, (newVal, oldVal) => {
  if (oldVal !== undefined && newVal !== oldVal && newVal > 0) {
    cartAnimating.value = true
    setTimeout(() => { cartAnimating.value = false }, 300)
  }
})

async function handleLogout() {
  try {
    await auth.logout()
  } catch { /* ignore */ }
  wishlist.clear()
  toast.info(t('toast.logout_success'))
  mobileOpen.value = false
}
</script>

<template>
  <nav class="bg-white dark:bg-gray-900 shadow-sm dark:shadow-none border-b border-gray-200 dark:border-gray-700 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16">
        <!-- Logo -->
        <div class="flex items-center">
          <RouterLink to="/" class="text-xl font-bold text-indigo-600 dark:text-indigo-400">
            TechStore
          </RouterLink>
          <div class="hidden sm:ml-8 sm:flex sm:space-x-4">
            <RouterLink to="/products" class="text-gray-700 dark:text-gray-200 hover:text-indigo-600 dark:hover:text-indigo-400 px-3 py-2 text-sm font-medium">
              {{ t('nav.products') }}
            </RouterLink>
          </div>
        </div>

        <!-- Right side -->
        <div class="hidden sm:flex sm:items-center sm:space-x-3">
          <!-- Dark mode toggle -->
          <button
            @click="themeStore.toggleTheme()"
            class="p-2 rounded-md text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer transition"
            :aria-label="themeStore.isDark ? t('nav.light_mode') : t('nav.dark_mode')"
            :title="themeStore.isDark ? 'Switch to light mode' : 'Switch to dark mode'"
          >
            <svg v-if="themeStore.isDark" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
          </button>

          <!-- Language switcher -->
          <button
            @click="toggleLocale"
            class="text-xs font-medium px-2.5 py-1.5 rounded-md border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer transition"
            :title="locale === 'en' ? 'Chuyển sang Tiếng Việt' : 'Switch to English'"
          >
            {{ locale === 'en' ? 'VI' : 'EN' }}
          </button>

          <!-- Wishlist -->
          <RouterLink v-if="auth.isAuthenticated" to="/wishlist" :aria-label="t('nav.wishlist')" class="relative text-gray-700 dark:text-gray-200 hover:text-red-500 dark:hover:text-red-400 p-2 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/>
            </svg>
            <span v-if="wishlist.count > 0" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
              {{ wishlist.count }}
            </span>
          </RouterLink>

          <!-- Cart -->
          <RouterLink to="/cart" :aria-label="t('nav.cart')" class="relative text-gray-700 dark:text-gray-200 hover:text-indigo-600 dark:hover:text-indigo-400 p-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" />
            </svg>
            <span v-if="cart.count > 0" :class="['absolute -top-1 -right-1 bg-indigo-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center', cartAnimating && 'animate-bounce-badge']">
              {{ cart.count }}
            </span>
          </RouterLink>

          <template v-if="auth.isAuthenticated">
            <RouterLink v-if="auth.isAdmin" to="/admin" class="text-gray-700 dark:text-gray-200 hover:text-indigo-600 dark:hover:text-indigo-400 px-3 py-2 text-sm font-medium">
              {{ t('nav.admin') }}
            </RouterLink>
            <RouterLink to="/orders" class="text-gray-700 dark:text-gray-200 hover:text-indigo-600 dark:hover:text-indigo-400 px-3 py-2 text-sm font-medium">
              {{ t('nav.orders') }}
            </RouterLink>
            <RouterLink to="/profile" class="text-gray-700 dark:text-gray-200 hover:text-indigo-600 dark:hover:text-indigo-400 px-3 py-2 text-sm font-medium">
              {{ auth.user?.name }}
            </RouterLink>
            <button @click="handleLogout" class="text-gray-500 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 px-3 py-2 text-sm font-medium cursor-pointer">
              {{ t('nav.logout') }}
            </button>
          </template>
          <template v-else>
            <RouterLink to="/login" class="text-gray-700 dark:text-gray-200 hover:text-indigo-600 dark:hover:text-indigo-400 px-3 py-2 text-sm font-medium">
              {{ t('nav.login') }}
            </RouterLink>
            <RouterLink to="/register" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">
              {{ t('nav.register') }}
            </RouterLink>
          </template>
        </div>

        <!-- Mobile menu button -->
        <div class="flex items-center sm:hidden gap-1">
          <button
            @click="themeStore.toggleTheme()"
            class="p-2 rounded-md text-gray-600 dark:text-gray-300 cursor-pointer"
          >
            <svg v-if="themeStore.isDark" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
          </button>
          <button
            @click="toggleLocale"
            class="text-xs font-medium px-2 py-1.5 rounded-md border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 cursor-pointer"
          >
            {{ locale === 'en' ? 'VI' : 'EN' }}
          </button>
          <RouterLink v-if="auth.isAuthenticated" to="/wishlist" :aria-label="t('nav.wishlist')" class="relative text-gray-700 dark:text-gray-200 p-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/>
            </svg>
            <span v-if="wishlist.count > 0" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center text-[10px]">
              {{ wishlist.count }}
            </span>
          </RouterLink>
          <RouterLink to="/cart" :aria-label="t('nav.cart')" class="relative text-gray-700 dark:text-gray-200 p-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" />
            </svg>
            <span v-if="cart.count > 0" :class="['absolute -top-1 -right-1 bg-indigo-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center', cartAnimating && 'animate-bounce-badge']">
              {{ cart.count }}
            </span>
          </RouterLink>
          <button @click="mobileOpen = !mobileOpen" aria-label="Open menu" class="text-gray-700 dark:text-gray-200 p-2 cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile menu -->
    <div v-if="mobileOpen" role="menu" class="sm:hidden border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900">
      <div class="px-4 py-3 space-y-2">
        <RouterLink to="/products" @click="mobileOpen = false" class="block text-gray-700 dark:text-gray-200 hover:text-indigo-600 dark:hover:text-indigo-400 py-2">{{ t('nav.products') }}</RouterLink>
        <template v-if="auth.isAuthenticated">
          <RouterLink v-if="auth.isAdmin" to="/admin" @click="mobileOpen = false" class="block text-gray-700 dark:text-gray-200 hover:text-indigo-600 dark:hover:text-indigo-400 py-2">{{ t('nav.admin') }}</RouterLink>
          <RouterLink to="/wishlist" @click="mobileOpen = false" class="block text-gray-700 dark:text-gray-200 hover:text-indigo-600 dark:hover:text-indigo-400 py-2">{{ t('nav.wishlist') }}</RouterLink>
          <RouterLink to="/orders" @click="mobileOpen = false" class="block text-gray-700 dark:text-gray-200 hover:text-indigo-600 dark:hover:text-indigo-400 py-2">{{ t('nav.orders') }}</RouterLink>
          <RouterLink to="/profile" @click="mobileOpen = false" class="block text-gray-700 dark:text-gray-200 hover:text-indigo-600 dark:hover:text-indigo-400 py-2">{{ t('nav.profile') }}</RouterLink>
          <button @click="handleLogout" class="block text-red-600 dark:text-red-400 py-2 cursor-pointer">{{ t('nav.logout') }}</button>
        </template>
        <template v-else>
          <RouterLink to="/login" @click="mobileOpen = false" class="block text-gray-700 dark:text-gray-200 hover:text-indigo-600 dark:hover:text-indigo-400 py-2">{{ t('nav.login') }}</RouterLink>
          <RouterLink to="/register" @click="mobileOpen = false" class="block text-indigo-600 dark:text-indigo-400 font-medium py-2">{{ t('nav.register') }}</RouterLink>
        </template>
      </div>
    </div>
  </nav>
</template>

<style scoped>
@keyframes bounce-badge {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.4); }
}
.animate-bounce-badge {
  animation: bounce-badge 0.3s ease-out;
}
</style>
