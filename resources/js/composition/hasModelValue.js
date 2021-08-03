import { computed } from 'vue'

export default (props, emit) => {
  const localValue = computed(
      () => props.modelValue,
      state => emit('update:modelValue', state)
    )

    return {
      localValue
    }
}
