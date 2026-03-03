import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/lib/axios'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('token'))

  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => user.value?.roles?.includes('admin'))

  async function login(credentials) {
    const { data } = await api.post('/auth/login', credentials)
    token.value = data.data.token
    user.value = data.data.user
    localStorage.setItem('token', data.data.token)
  }

  async function register(payload) {
    const { data } = await api.post('/auth/register', payload)
    token.value = data.data.token
    user.value = data.data.user
    localStorage.setItem('token', data.data.token)
  }

  async function logout() {
    await api.post('/auth/logout')
    token.value = null
    user.value = null
    localStorage.removeItem('token')
  }

  async function fetchUser() {
    if (!token.value) return
    const { data } = await api.get('/auth/me')
    user.value = data.data
  }

  return { user, token, isAuthenticated, isAdmin, login, register, logout, fetchUser }
})
