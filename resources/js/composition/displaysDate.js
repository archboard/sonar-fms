import { computed } from 'vue'
import dayjs from '@/plugins/dayjs'
import { usePage } from '@inertiajs/inertia-vue3'

export default () => {
  const page = usePage()
  const timezone = computed(() => page.props.value.user?.timezone || 'UTC')

  const displayDate = (date, format) => {
    return dayjs(date).tz(timezone.value).format(format)
  }

  return {
    timezone,
    displayDate,
  }
}
