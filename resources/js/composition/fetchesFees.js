import { inject, ref } from 'vue'

export default () => {
  const $route = inject('$route')
  const $http = inject('$http')
  const fees = ref([])
  const fetchFees = () => {
    $http.get($route('fees.all')).then(({ data }) => {
      fees.value = data
    })
  }

  fetchFees()

  return {
    fees,
    fetchFees,
  }
}
