import { computed } from 'vue'

export default (props, emit) => {
  const localValue = computed({
    get: () => props.modelValue,
    set: state => emit('update:modelValue', state)
  })

  return {
    localValue
  }
}
