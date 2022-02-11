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
  const formats = {
    full: 'MMMM D, YYYY h:mma',
    abbr: 'MMM D, YYYY h:mma',
    abbr_date: 'MMM D, YYYY',
  }

  const getDate = (date, offset = false) => (date ? dayjs(date) : dayjs()).tz(timezone.value, offset)
  const displayDate = (date, format) => getDate(date).format(formats[format] || format)
  const fromNow = (date) => getDate(date).fromNow()

  return {
    timezone,
    displayDate,
    getDate,
    fromNow,
  }
}
