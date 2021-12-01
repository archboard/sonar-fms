import { inject, ref } from 'vue'
import omit from 'lodash/omit'

export default (forImport, routeBase) => {
  const $http = inject('$http')
  const templates = ref([])

  const fetchTemplates = () => {
    const params = forImport
      ? { for_import: 1 }
      : {}

    $http.get(routeBase, { params }).then(({ data }) => {
      templates.value = data
    })
  }
  const saveTemplate = async form => {
    const route = form.id
      ? `${routeBase}/${form.id}`
      : routeBase
    const method = form.id
      ? 'put'
      : 'post'
    const data = form.data()

    await $http[method](route, omit(data, ['template.students']))
    fetchTemplates()
  }
  const deleteTemplate = template => {
    $http.delete(`${routeBase}/${template.id}`)
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
