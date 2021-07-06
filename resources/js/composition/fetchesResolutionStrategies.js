import { inject } from 'vue'
import strategies from '@/stores/resolutionStretegies'

export default () => {
  const $route = inject('$route')
  const $http = inject('$http')
  const fetchStrategies = () => {
    $http.get($route('resolution-strategies.all')).then(({ data }) => {
      strategies.value = data
    })
  }

  if (strategies.value.length === 0) {
    fetchStrategies()
  }

  return {
    strategies,
    fetchStrategies,
  }
}
