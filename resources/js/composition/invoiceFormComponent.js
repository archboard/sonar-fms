import { computed } from 'vue'
import displaysCurrency from '@/composition/displaysCurrency'

export default (props, { emit }) => {
  const localValue = computed(
    () => props.modelValue,
    state => emit('update:modelValue', state)
  )
  const { displayCurrency } = displaysCurrency()

  return {
    localValue,
    displayCurrency,
  }
}
