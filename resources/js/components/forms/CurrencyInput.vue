<template>
  <div class="relative">
    <div class="absolute top-0 bottom-0 left-0 flex items-center justify-center px-4">
      <CashIcon class="w-4 h-4 text-gray-500 dark:text-gray-400" />
    </div>
    <input
      :id="id"
      type="text"
      class="pl-10 shadow-sm focus:ring-2 focus:ring-primary-500 focus:ring-offset-primary-500 focus:border-primary-500 block w-full border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-900 dark:focus:border-primary-500 transition duration-150 ease-in-out"
      ref="inputRef"
      :placeholder="displayCurrency(0)"
    />
  </div>
</template>

<script>
import { defineComponent, watch } from 'vue'
import { useCurrencyInput } from 'vue-currency-input'
import displaysCurrency from '@/composition/displaysCurrency'
import { CashIcon } from '@heroicons/vue/outline'

export default defineComponent({
  components: {
    CashIcon,
  },
  props: {
    id: String,
    modelValue: {
      type: [String, Number],
    },
  },

  setup (props) {
    const { currency, locale, displayCurrency } = displaysCurrency()
    const currencyOptions = {
      currency: currency.code,
      currencyDisplay: 'narrowSymbol',
      locale,
      valueRange: {
        min: 0
      },
      hideCurrencySymbolOnFocus: true,
      hideGroupingSeparatorOnFocus: true,
      hideNegligibleDecimalDigitsOnFocus: true,
      autoDecimalDigits: false,
      valueScaling: 'precision',
      autoSign: true,
      useGrouping: true,
    }
    const {
      inputRef,
      setValue
    } = useCurrencyInput(currencyOptions)

    watch(() => props.modelValue, value => {
      setValue(value)
    })

    return {
      inputRef,
      displayCurrency,
    }
  }
})
</script>
