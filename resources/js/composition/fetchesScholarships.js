import { inject } from 'vue'
import scholarships from '@/stores/scholarships'

export default () => {
  const $route = inject('$route')
  const $http = inject('$http')
  const fetchScholarships = () => {
    $http.get($route('scholarships.all')).then(({ data }) => {
      scholarships.value = data
    })
  }

  // if (scholarships.value.length === 0) {
    fetchScholarships()
  // }

  return {
    scholarships,
    fetchScholarships,
  }
}
