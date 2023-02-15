import { reactive, watch } from 'vue'
import qs from 'qs'
import { router } from '@inertiajs/vue3'
import pick from 'lodash/pick'
import pickBy from 'lodash/pickBy'

export default (defaultFilters, route, casts = {}) => {
  const castValues = object => {
    Object.keys(casts).forEach((key) => {
      if (typeof object[key] !== 'undefined') {
        const cast = casts[key].toLowerCase()

        if (cast.includes('bool')) {
          object[key] = !!(object[key] === true ||
            object[key] === 1 ||
            object[key] === '1' ||
            object[key] === 'true')
        } else if (cast.includes('int')) {
          object[key] = +object[key]
        }
      }
    })

    return object
  }
  const qsFilters = castValues(qs.parse(window.location.search.substr(1)))
  const filters = reactive(Object.assign({}, defaultFilters, {
    ...qsFilters,
  }))

  watch(filters, () => {
    const data = pickBy(pick(filters, Object.keys(defaultFilters)))
    const url = `${route}?${qs.stringify(data)}`

    router.visit(url, {
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
    filters.page = 1

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
