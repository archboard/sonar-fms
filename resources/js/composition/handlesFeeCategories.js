import { inject, ref } from 'vue'

export default () => {
  const $http = inject('$http')
  const $route = inject('$route')
  const categories = ref([])

  const fetchCategories = () => {
    $http.get($route('fee-categories.index')).then(({ data }) => {
      categories.value = data
    })
  }
  const saveCategory = form => {
    const route = form.id
      ? $route('fee-categories.update', form.id)
      : $route('fee-categories.store')
    const method = form.id
      ? 'put'
      : 'post'

    form[method](route, {
      preserveScroll: true,
      onSuccess () {
        form.reset()
        fetchCategories()
      }
    })
  }

  fetchCategories()

  return {
    categories,
    fetchCategories,
    saveCategory,
  }
}
