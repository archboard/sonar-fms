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

  const applyFilters = newFilters => {
    Object.keys(newFilters).forEach(key => {
      filters[key] = newFilters[key]
    })
  }

  const resetFilters = () => applyFilters(defaultFilters)

  const sortColumn = column => {
    if (column === filters.orderBy) {
      filters.orderDir = filters.orderDir === 'asc'
        ? 'desc'
        : 'asc'
    } else {
      filters.orderBy = column
      filters.orderDir = 'asc'
    }
  }

  return {
    filters,
    applyFilters,
    resetFilters,
    sortColumn,
  }
}
