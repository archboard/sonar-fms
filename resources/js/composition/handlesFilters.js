import { reactive, watch } from 'vue'
import qs from 'qs'
import { Inertia } from '@inertiajs/inertia'
import pick from 'lodash/pick'
import pickBy from 'lodash/pickBy'
import debounce from 'lodash/debounce'

export default (defaultFilters, route) => {
  const qsFilters = qs.parse(window.location.search.substr(1))
  const filters = reactive(Object.assign({}, defaultFilters, qsFilters))
  watch(filters, () => {
    const data = pickBy(pick(filters, Object.keys(defaultFilters)))
    const url = `${route}?${qs.stringify(data)}`

    Inertia.visit(url, {
      preserveScroll: true,
      preserveState: true,
    })
  })

  return filters
}
