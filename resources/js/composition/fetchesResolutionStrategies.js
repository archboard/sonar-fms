import { inject, ref } from 'vue'

export default () => {
  const $route = inject('$route')
  const $http = inject('$http')
  const strategies = ref([])
  const fetchStrategies = () => {
    $http.get($route('resolution-strategies.all')).then(({ data }) => {
      strategies.value = data
    })
  }

  fetchStrategies()

  return {
    strategies,
    fetchStrategies,
  }
}
