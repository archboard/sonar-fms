import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

export default (prop) => {
  return computed(() => usePage().props?.[prop])
}
