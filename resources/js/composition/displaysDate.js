import { computed, onUnmounted, ref } from 'vue'
import dayjs from '@/plugins/dayjs'
import { usePage } from '@inertiajs/inertia-vue3'

export default () => {
  const page = usePage()
  const timezone = computed(() =>
    page.props.value.user?.timezone ||
    page.props.value.school?.timezone ||
    'UTC'
  )
  const timeFormats = {
    '12': 'h:mma',
    '24': 'H:mm'
  }
  const timeFormat = computed(() => {
    return timeFormats[page.props.value.user?.time_format] || timeFormats['12']
  })
  const formats = {
    full: `MMMM D, YYYY ${timeFormat.value}`,
    abbr: `MMM D, YYYY ${timeFormat.value}`,
    time: timeFormat.value,
    abbr_date: 'MMM D, YYYY',
  }
  const getDate = (date, offset = false) => (date ? dayjs(date) : dayjs()).tz(timezone.value, offset)
  const displayDate = (date, format, offset = false) => getDate(date, offset).format(formats[format] || format)
  const fromNow = (date) => getDate(date).fromNow()
  const realtimeNow = ref(getDate())
  const interval = setInterval(() => {
    realtimeNow.value = getDate()
  }, 1000)

  onUnmounted(() => {
    clearInterval(interval)
  })

  return {
    timezone,
    timeFormats,
    displayDate,
    getDate,
    fromNow,
    realtimeNow,
  }
}
