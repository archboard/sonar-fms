import { computed } from 'vue'
import dayjs from '@/plugins/dayjs'
import { usePage } from '@inertiajs/inertia-vue3'

export default () => {
  const page = usePage()
  const timezone = computed(() =>
    page.props.value.user?.timezone ||
    page.props.value.school?.timezone ||
    'UTC'
  )

  const getDate = (date) => dayjs(date).tz(timezone.value)
  const displayDate = (date, format) => getDate(date).format(format)

  return {
    timezone,
    displayDate,
    getDate,
  }
}
