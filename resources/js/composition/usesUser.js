import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

export default () => {
  const user = computed(() => usePage().props.user)

  return {
    user,
  }
}
