<script setup>
import { RouterLink, RouterView } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()

const navItems = [
  { to: '/admin/dashboard', label: 'Dashboard' },
  { to: '/admin/products', label: 'Products' },
  { to: '/admin/categories', label: 'Categories' },
  { to: '/admin/orders', label: 'Orders' },
]
</script>

<template>
  <div class="min-h-screen flex bg-gray-50">
    <!-- Sidebar -->
    <aside class="w-64 bg-gray-900 text-white flex-shrink-0 hidden lg:block">
      <div class="p-6">
        <RouterLink to="/admin" class="text-xl font-bold text-white">TechStore Admin</RouterLink>
      </div>
      <nav class="px-3 space-y-1">
        <RouterLink
          v-for="item in navItems"
          :key="item.to"
          :to="item.to"
          class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-300 hover:bg-gray-800 hover:text-white transition"
          active-class="!bg-indigo-600 !text-white"
        >
          {{ item.label }}
        </RouterLink>
      </nav>
      <div class="mt-auto p-6 border-t border-gray-800">
        <p class="text-sm text-gray-400">{{ auth.user?.name }}</p>
        <RouterLink to="/" class="text-xs text-gray-500 hover:text-gray-300">Back to store &rarr;</RouterLink>
      </div>
    </aside>

    <!-- Mobile header -->
    <div class="lg:hidden fixed top-0 left-0 right-0 bg-gray-900 text-white p-4 z-50 flex items-center justify-between">
      <span class="font-bold">Admin</span>
      <div class="flex gap-3 text-sm">
        <RouterLink v-for="item in navItems" :key="item.to" :to="item.to" class="text-gray-300 hover:text-white" active-class="!text-white underline">
          {{ item.label }}
        </RouterLink>
      </div>
    </div>

    <!-- Main content -->
    <main class="flex-1 p-6 lg:p-8 lg:mt-0 mt-14">
      <RouterView />
    </main>
  </div>
</template>
