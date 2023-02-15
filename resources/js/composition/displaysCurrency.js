import { usePage } from '@inertiajs/vue3'

export default () => {
  const { props } = usePage()
  const currency = props.school?.currency || { code: 'USD' }
  const locale = props.user?.locale || 'en'
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
