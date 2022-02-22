import { usePage } from '@inertiajs/inertia-vue3'
import { computed } from 'vue'

export default () => {
  const page = usePage()
  const user = computed(() => page.props.value.user)

  return {
    user,
  }
}
