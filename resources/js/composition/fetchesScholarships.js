import { inject, ref } from 'vue'

export default () => {
  const $route = inject('$route')
  const $http = inject('$http')
  const scholarships = ref([])
  const fetchScholarships = () => {
    $http.get($route('scholarships.all')).then(({ data }) => {
      scholarships.value = data
    })
  }

  fetchScholarships()

  return {
    scholarships,
    fetchScholarships,
  }
}
