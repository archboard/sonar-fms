import { onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'

export default () => {
  onMounted(() => {
    const page = usePage()

    document.title = page.props.title
      ? `${page.props.title} | Sonar FMS`
      : 'Sonar FMS'
  })
}
