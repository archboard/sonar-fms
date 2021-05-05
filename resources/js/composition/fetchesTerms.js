import { inject, ref } from 'vue'

export default () => {
  const $route = inject('$route')
  const $http = inject('$http')
  const terms = ref([])
  const fetchTerms = () => {
    $http.get($route('terms.index')).then(({ data }) => {
      terms.value = data
    })
  }

  fetchTerms()

  return {
    terms,
    fetchTerms,
  }
}
