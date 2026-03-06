import { ref } from 'vue'
import { useToast } from './useToast'
import { useI18n } from 'vue-i18n'

export function useUndoDelete() {
  const { t } = useI18n()
  const toast = useToast()
  const pendingDeletes = ref(new Map())

  function scheduleDelete(id, deleteFn, itemName = 'Item', delay = 5000) {
    const timeoutId = setTimeout(async () => {
      pendingDeletes.value.delete(id)
      await deleteFn(id)
    }, delay)

    pendingDeletes.value.set(id, timeoutId)

    toast.warning(t('admin.deleted_undo', { item: itemName }), delay, {
      label: t('admin.undo'),
      callback: () => {
        clearTimeout(timeoutId)
        pendingDeletes.value.delete(id)
        toast.success(t('admin.undo_success'))
      },
    })
  }

  function cancelAll() {
    for (const timeoutId of pendingDeletes.value.values()) {
      clearTimeout(timeoutId)
    }
    pendingDeletes.value.clear()
  }

  function isPending(id) {
    return pendingDeletes.value.has(id)
  }

  return { scheduleDelete, cancelAll, isPending }
}
