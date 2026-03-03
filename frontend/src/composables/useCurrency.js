import { useI18n } from 'vue-i18n'

const USD_TO_VND = 25000

const fmtUSD = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' })
const fmtVND = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND', maximumFractionDigits: 0 })

export function useCurrency() {
  const { locale } = useI18n()

  function formatPrice(priceUSD) {
    const num = Number(priceUSD)
    if (locale.value === 'vi') {
      return fmtVND.format(Math.round(num * USD_TO_VND))
    }
    return fmtUSD.format(num)
  }

  return { formatPrice }
}
