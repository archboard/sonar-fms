<template>
  <Select v-model="localValue">
    <option :value="null">{{ __('N/A') }}</option>
    <option v-for="(driver, name) in paymentMethods" :key="name" :value="driver.payment_method.id">{{ driver.label }}</option>
  </Select>
</template>

<script>
import { defineComponent } from 'vue'
import Select from '@/components/forms/Select.vue'
import hasModelValue from '@/composition/hasModelValue.js'
import fetchesPaymentMethods from '@/composition/fetchesPaymentMethods.js'

export default defineComponent({
  components: {
    Select,
  },

  setup (props, { emit }) {
    const { localValue } = hasModelValue(props, emit)
    const { paymentMethods } = fetchesPaymentMethods()

    return {
      localValue,
      paymentMethods,
    }
  }
})
</script>
