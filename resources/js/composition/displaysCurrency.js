import { usePage } from '@inertiajs/inertia-vue3'

export default () => {
  const { props } = usePage()
  const currency = props.value.school?.currency || { code: 'USD' }
  const locale = props.value.user?.locale
  const displayCurrency = amount => {
    const asFloat = (amount || 0) / Math.pow(10, (currency?.digits || 2))

    return new Intl.NumberFormat(locale || 'en', { style: 'currency', currency: currency.code }).format(asFloat)
  }

  return {
    displayCurrency,
    currency,
    locale,
  }
}
