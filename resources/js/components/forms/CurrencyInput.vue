<template>
  <div>
    <input
      :id="id"
      type="text"
      class="shadow-sm focus:ring-2 focus:ring-primary-500 focus:ring-offset-primary-500 focus:border-primary-500 block w-full border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-900 dark:focus:border-primary-500 transition duration-150 ease-in-out"
      ref="inputRef"
    />
  </div>
</template>

<script>
import { defineComponent, watch } from 'vue'
import useCurrencyInput from 'vue-currency-input'
import displaysCurrency from '../../composition/displaysCurrency'

export default defineComponent({
  components: {
  },
  props: {
    id: String,
    modelValue: {
      type: [String, Number],
    },
  },

  setup (props) {
    const { currency, locale } = displaysCurrency()
    const currencyOptions = {
      currency: currency.code,
      locale,
      valueRange: {
        min: 0
      },
      hideCurrencySymbolOnFocus: true,
      hideGroupingSeparatorOnFocus: true,
      hideNegligibleDecimalDigitsOnFocus: true,
      autoDecimalDigits: false,
      exportValueAsInteger: true,
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
    }
  }
})
</script>
