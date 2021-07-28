import { usePage } from '@inertiajs/inertia-vue3'
import { computed } from 'vue'

export default () => {
  const page = usePage()
  const school = computed(() => page.props.value.school)

  return {
    school,
  }
}
