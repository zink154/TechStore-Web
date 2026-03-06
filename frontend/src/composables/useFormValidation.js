import { reactive, computed } from 'vue'

export function useFormValidation(rules) {
  const errors = reactive({})

  function validate(field, value) {
    const fieldRules = rules[field]
    if (!fieldRules) return true

    for (const rule of fieldRules) {
      const result = rule(value)
      if (result !== true) {
        errors[field] = result
        return false
      }
    }
    errors[field] = ''
    return true
  }

  function validateAll(form) {
    let valid = true
    for (const field of Object.keys(rules)) {
      if (!validate(field, form[field])) {
        valid = false
      }
    }
    return valid
  }

  function clearErrors() {
    for (const key of Object.keys(errors)) {
      errors[key] = ''
    }
  }

  const isValid = computed(() => Object.values(errors).every(e => !e))

  return { errors, validate, validateAll, clearErrors, isValid }
}
