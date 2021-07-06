import { inject } from 'vue'
import terms from '@/stores/terms'

export default () => {
  const $route = inject('$route')
  const $http = inject('$http')
  const fetchTerms = () => {
    $http.get($route('terms.index')).then(({ data }) => {
      terms.value = data
    })
  }

  if (terms.value.length === 0) {
    fetchTerms()
  }

  return {
    terms,
    fetchTerms,
  }
}
