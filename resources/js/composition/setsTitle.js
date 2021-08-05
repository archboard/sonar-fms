import { onMounted } from 'vue'
import { usePage } from '@inertiajs/inertia-vue3'

export default () => {
  const page = usePage()

  onMounted(() => {
    document.title = page.props.value.title
      ? `${page.props.value.title} | Sonar FMS`
      : 'Sonar FMS'
  })
}
