<template>
  <Select v-model="localValue">
    <option :value="null">{{ __('Do not map') }}</option>
    <option
      v-for="header in headers"
      :key="header"
      :value="header"
    >
      {{ header }}
    </option>
  </Select>
</template>

<script>
import { computed, defineComponent, ref } from 'vue'
import Select from '@/components/forms/Select'

export default defineComponent({
  components: {
    Select,
  },

  props: {
    headers: Array,
    modelValue: String,
  },
  emits: ['update:modelValue'],

  setup (props, { emit }) {
    const localValue = computed({
      get: () => props.modelValue,
      set: value => emit('update:modelValue', value)
    })

    return {
      localValue,
    }
  }
})
</script>
