import { inject, ref } from 'vue'

export default () => {
  const $http = inject('$http')
  const $route = inject('$route')
  const departments = ref([])

  const fetchDepartments = () => {
    $http.get($route('departments.index')).then(({ data }) => {
      departments.value = data
    })
  }
  const saveDepartment = form => {
    const route = form.id
      ? $route('departments.update', form.id)
      : $route('departments.store')
    const method = form.id
      ? 'put'
      : 'post'

    form[method](route, {
      preserveScroll: true,
      onSuccess () {
        form.reset()
        fetchDepartments()
      }
    })
  }

  fetchDepartments()

  return {
    departments,
    fetchDepartments,
    saveDepartment,
  }
}
