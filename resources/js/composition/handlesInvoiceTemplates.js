import { inject, ref } from 'vue'

export default () => {
  const $http = inject('$http')
  const $route = inject('$route')
  const templates = ref([])

  const fetchTemplates = () => {
    $http.get($route('templates.index')).then(({ data }) => {
      templates.value = data
    })
  }
  const saveTemplate = async form => {
    const route = form.id
      ? $route('templates.update', form.id)
      : $route('templates.store')
    const method = form.id
      ? 'put'
      : 'post'

    await $http[method](route, form.data())
    fetchTemplates()
  }
  const deleteTemplate = template => {
    $http.delete($route('templates.destroy', template))
      .then(fetchTemplates)
  }

  fetchTemplates()

  return {
    templates,
    fetchTemplates,
    saveTemplate,
    deleteTemplate,
  }
}
