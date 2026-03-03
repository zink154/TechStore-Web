<script setup>
import { RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useCartStore } from '@/stores/cart'
import { ref } from 'vue'

const auth = useAuthStore()
const cart = useCartStore()
const mobileOpen = ref(false)

async function handleLogout() {
  await auth.logout()
  mobileOpen.value = false
}
</script>

<template>
  <nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16">
        <!-- Logo -->
        <div class="flex items-center">
          <RouterLink to="/" class="text-xl font-bold text-indigo-600">
            TechStore
          </RouterLink>
          <div class="hidden sm:ml-8 sm:flex sm:space-x-4">
            <RouterLink to="/products" class="text-gray-700 hover:text-indigo-600 px-3 py-2 text-sm font-medium">
              Products
            </RouterLink>
          </div>
        </div>

        <!-- Right side -->
        <div class="hidden sm:flex sm:items-center sm:space-x-4">
          <RouterLink to="/cart" class="relative text-gray-700 hover:text-indigo-600 p-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" />
            </svg>
            <span v-if="cart.count > 0" class="absolute -top-1 -right-1 bg-indigo-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
              {{ cart.count }}
            </span>
          </RouterLink>

          <template v-if="auth.isAuthenticated">
            <RouterLink v-if="auth.isAdmin" to="/admin" class="text-gray-700 hover:text-indigo-600 px-3 py-2 text-sm font-medium">
              Admin
            </RouterLink>
            <RouterLink to="/orders" class="text-gray-700 hover:text-indigo-600 px-3 py-2 text-sm font-medium">
              Orders
            </RouterLink>
            <RouterLink to="/profile" class="text-gray-700 hover:text-indigo-600 px-3 py-2 text-sm font-medium">
              {{ auth.user?.name }}
            </RouterLink>
            <button @click="handleLogout" class="text-gray-500 hover:text-red-600 px-3 py-2 text-sm font-medium cursor-pointer">
              Logout
            </button>
          </template>
          <template v-else>
            <RouterLink to="/login" class="text-gray-700 hover:text-indigo-600 px-3 py-2 text-sm font-medium">
              Login
            </RouterLink>
            <RouterLink to="/register" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">
              Register
            </RouterLink>
          </template>
        </div>

        <!-- Mobile menu button -->
        <div class="flex items-center sm:hidden">
          <RouterLink to="/cart" class="relative text-gray-700 p-2 mr-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" />
            </svg>
            <span v-if="cart.count > 0" class="absolute -top-1 -right-1 bg-indigo-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
              {{ cart.count }}
            </span>
          </RouterLink>
          <button @click="mobileOpen = !mobileOpen" class="text-gray-700 p-2 cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile menu -->
    <div v-if="mobileOpen" class="sm:hidden border-t border-gray-200 bg-white">
      <div class="px-4 py-3 space-y-2">
        <RouterLink to="/products" @click="mobileOpen = false" class="block text-gray-700 hover:text-indigo-600 py-2">Products</RouterLink>
        <template v-if="auth.isAuthenticated">
          <RouterLink v-if="auth.isAdmin" to="/admin" @click="mobileOpen = false" class="block text-gray-700 hover:text-indigo-600 py-2">Admin</RouterLink>
          <RouterLink to="/orders" @click="mobileOpen = false" class="block text-gray-700 hover:text-indigo-600 py-2">Orders</RouterLink>
          <RouterLink to="/profile" @click="mobileOpen = false" class="block text-gray-700 hover:text-indigo-600 py-2">Profile</RouterLink>
          <button @click="handleLogout" class="block text-red-600 py-2 cursor-pointer">Logout</button>
        </template>
        <template v-else>
          <RouterLink to="/login" @click="mobileOpen = false" class="block text-gray-700 hover:text-indigo-600 py-2">Login</RouterLink>
          <RouterLink to="/register" @click="mobileOpen = false" class="block text-indigo-600 font-medium py-2">Register</RouterLink>
        </template>
      </div>
    </div>
  </nav>
</template>
