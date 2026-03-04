import { ref } from 'vue'

const toasts = ref([])
let nextId = 0

export function useToast() {
  function addToast(message, type = 'success', duration = 3000) {
    const id = nextId++
    toasts.value.push({ id, message, type })
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

  return { toasts, success, error, info, removeToast }
}
