import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useThemeStore = defineStore('theme', () => {
  function getInitialTheme() {
    const stored = localStorage.getItem('theme')
    if (stored === 'dark' || stored === 'light') return stored
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
  }

  const theme = ref(getInitialTheme())
  const isDark = ref(theme.value === 'dark')

  function applyTheme() {
    const html = document.documentElement
    html.classList.add('theme-transition')

    if (theme.value === 'dark') {
      html.classList.add('dark')
    } else {
      html.classList.remove('dark')
    }
    isDark.value = theme.value === 'dark'

    setTimeout(() => html.classList.remove('theme-transition'), 300)
  }

  function toggleTheme() {
    theme.value = theme.value === 'dark' ? 'light' : 'dark'
    localStorage.setItem('theme', theme.value)
    applyTheme()
  }

  // Apply on store creation
  applyTheme()

  return { theme, isDark, toggleTheme }
})
