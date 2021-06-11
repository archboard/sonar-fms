import { usePage } from '@inertiajs/inertia-vue3'

export default () => {
  const { props } = usePage()
  const currency = props.value.school?.currency || { code: 'USD' }
  const locale = props.value.user?.locale || 'en'
  const displayCurrency = amount => {
    const asFloat = (amount || 0) / Math.pow(10, (currency?.digits || 2))
    const options = {
      style: 'currency',
      currency: currency.code
    }

    return new Intl.NumberFormat(locale, options)
      .format(asFloat)
  }

  return {
    displayCurrency,
    currency,
    locale,
  }
}
