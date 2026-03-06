import { ref } from 'vue'

const toasts = ref([])
let nextId = 0

export function useToast() {
  function addToast(message, type = 'success', duration = 3000, action = null) {
    const id = nextId++
    toasts.value.push({ id, message, type, action })
    if (duration > 0) {
      setTimeout(() => removeToast(id), duration)
    }
  }

  function removeToast(id) {
    toasts.value = toasts.value.filter((t) => t.id !== id)
  }

  function success(message) { addToast(message, 'success') }
  function error(message) { addToast(message, 'error', 5000) }
  function info(message) { addToast(message, 'info') }
  function warning(message, duration = 5000, action = null) { addToast(message, 'warning', duration, action) }

  return { toasts, success, error, info, warning, removeToast }
}
