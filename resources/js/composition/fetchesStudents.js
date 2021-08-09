import { inject, ref } from 'vue'

export default () => {
  const $route = inject('$route')
  const $http = inject('$http')
  const fetchingStudents = ref(false)
  const students = ref([])
  const search = async req => {
    fetchingStudents.value = true

    try {
      const { data } = await $http.post($route('students.search'), req)
      students.value = data
    } catch (err) {
      console.error(err)
    }

    fetchingStudents.value = false
  }

  return {
    students,
    search,
    fetchingStudents,
  }
}
