import { inject, ref } from 'vue'

export default () => {
  const $http = inject('$http')
  const paymentMethods = ref({})
  const fetchPaymentMethods = async () => {
    const { data } = await $http.get('/payment-methods/all')
    paymentMethods.value = data
  }

  fetchPaymentMethods()

  return {
    paymentMethods,
    fetchPaymentMethods,
  }
}
