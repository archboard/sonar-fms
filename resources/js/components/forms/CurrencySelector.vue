<template>
  <Select v-model="localValue">
    <option :value="null" disabled>{{ __('Select option') }}</option>
    <option
      v-for="currency in currencies"
      :key="currency.id"
      :value="currency.id"
    >
      {{ currency.currency }} ({{ currency.code }})
    </option>
  </Select>
</template>

<script>
import { defineComponent, ref, watch } from 'vue'
import Select from '@/components/forms/Select'

export default defineComponent({
  components: {
    Select,
  },
  emits: ['update:modelValue'],
  props: {
    modelValue: {
      type: [String, Number],
    },
    currencies: Array,
  },

  setup (props, { emit }) {
    const findCurrency = id => props.currencies.find(c => c.id === id)
    const localValue = ref(findCurrency(props.modelValue)?.id || null)

    watch(localValue, (newVal) => {
      emit('update:modelValue', newVal)
    })

    return {
      localValue,
    }
  }
})
</script>
