import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

export default () => {
  const school = computed(() => usePage().props.school)

  return {
    school,
  }
}
