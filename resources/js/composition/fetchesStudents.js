import { inject, ref } from 'vue'

export default () => {
  const $route = inject('$route')
  const $http = inject('$http')
  const students = ref([])
  const search = async req => {
    try {
      const { data } = await $http.post($route('students.search', req))
      students.value = data
    } catch (err) {
      console.error(err)
    }
  }

  return {
    students,
    search,
  }
}
