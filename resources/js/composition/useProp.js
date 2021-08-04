import { usePage } from '@inertiajs/inertia-vue3'
import { computed } from 'vue'

export default (prop) => {
  const page = usePage()
  return computed(() => page.props.value[prop])
}
