import { ref, watch } from 'vue'
import debounce from 'lodash/debounce'

export default (filters) => {
  const searchTerm = ref(filters.s)
  watch(searchTerm, debounce(newVal => {
    filters.s = newVal
    filters.page = 1
  }, 500))

  return {
    searchTerm
  }
}
