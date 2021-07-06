import { inject, ref } from 'vue'
import fees from '@/stores/fees'

export default () => {
  const $route = inject('$route')
  const $http = inject('$http')
  const fetchFees = () => {
    $http.get($route('fees.all')).then(({ data }) => {
      fees.value = data
    })
  }

  // if (fees.value.length === 0) {
    fetchFees()
  // }

  return {
    fees,
    fetchFees,
  }
}
