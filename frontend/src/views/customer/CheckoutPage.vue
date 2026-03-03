<script setup>
import DefaultLayout from '@/components/layout/DefaultLayout.vue'
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import { loadStripe } from '@stripe/stripe-js'
import api from '@/lib/axios'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const router = useRouter()
const cart = useCartStore()
const auth = useAuthStore()

const form = ref({
  shipping_address: auth.user?.address ?? '',
  phone: auth.user?.phone ?? '',
  notes: '',
})
const error = ref('')
const loading = ref(false)
const stripe = ref(null)
const cardElement = ref(null)
const cardMounted = ref(false)

onMounted(async () => {
  if (cart.items.length === 0) {
    router.push('/cart')
    return
  }

  const stripeKey = import.meta.env.VITE_STRIPE_KEY
  if (stripeKey && !stripeKey.includes('your_stripe')) {
    stripe.value = await loadStripe(stripeKey)
    const elements = stripe.value.elements()
    cardElement.value = elements.create('card', {
      style: {
        base: { fontSize: '16px', color: '#374151' },
      },
    })
    cardElement.value.mount('#card-element')
    cardMounted.value = true
  }
})

async function handleCheckout() {
  error.value = ''
  loading.value = true

  try {
    const items = cart.items.map((i) => ({ product_id: i.id, quantity: i.quantity }))

    const { data: piData } = await api.post('/checkout/payment-intent', { items })

    let paymentIntentId = 'demo_payment'

    if (stripe.value && cardMounted.value) {
      const { error: stripeError, paymentIntent } = await stripe.value.confirmCardPayment(
        piData.data.client_secret,
        { payment_method: { card: cardElement.value } }
      )
      if (stripeError) {
        error.value = stripeError.message
        loading.value = false
        return
      }
      paymentIntentId = paymentIntent.id
    }

    await api.post('/checkout', {
      items,
      shipping_address: form.value.shipping_address,
      phone: form.value.phone,
      notes: form.value.notes,
      payment_intent_id: paymentIntentId,
    })

    cart.clearCart()
    router.push('/orders')
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Checkout failed.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <DefaultLayout>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-6">{{ t('checkout.title') }}</h1>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form -->
        <form @submit.prevent="handleCheckout" class="lg:col-span-2 space-y-6">
          <div v-if="error" class="bg-red-50 text-red-600 text-sm p-3 rounded-lg">{{ error }}</div>

          <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">{{ t('checkout.shipping') }}</h2>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('checkout.address') }}</label>
              <textarea v-model="form.shipping_address" required rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"></textarea>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('checkout.phone') }}</label>
              <input v-model="form.phone" type="tel" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('checkout.notes') }}</label>
              <textarea v-model="form.notes" rows="2" :placeholder="t('checkout.notes_placeholder')" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"></textarea>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ t('checkout.payment') }}</h2>
            <div id="card-element" class="border border-gray-300 rounded-lg p-3"></div>
            <p v-if="!cardMounted" class="text-sm text-gray-400 mt-2">{{ t('checkout.stripe_not_configured') }}</p>
          </div>

          <button type="submit" :disabled="loading" class="w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 disabled:opacity-50 cursor-pointer">
            {{ loading ? t('checkout.placing') : `${t('checkout.place_order')} — $${cart.total.toFixed(2)}` }}
          </button>
        </form>

        <!-- Order Summary -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 h-fit">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ t('checkout.summary') }}</h2>
          <div class="space-y-3">
            <div v-for="item in cart.items" :key="item.id" class="flex justify-between text-sm">
              <span class="text-gray-600">{{ item.name }} x{{ item.quantity }}</span>
              <span class="font-medium">${{ (item.price * item.quantity).toFixed(2) }}</span>
            </div>
          </div>
          <div class="border-t border-gray-200 mt-4 pt-4 flex justify-between">
            <span class="font-semibold text-gray-900">Total</span>
            <span class="font-bold text-lg text-gray-900">${{ cart.total.toFixed(2) }}</span>
          </div>
        </div>
      </div>
    </div>
  </DefaultLayout>
</template>
