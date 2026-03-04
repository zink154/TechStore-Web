import { watch, onMounted } from 'vue'

export function useMeta(titleRef) {
  function update() {
    const title = typeof titleRef === 'function' ? titleRef() : titleRef.value
    if (title) {
      document.title = `${title} | TechStore`
    }
  }

  onMounted(update)

  if (typeof titleRef !== 'function' && titleRef?.value !== undefined) {
    watch(titleRef, update)
  }
}
