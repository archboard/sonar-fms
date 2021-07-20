<template>
  <Select v-model="localValue">
    <option :value="null" disabled>{{ __('Select timezone') }}</option>
    <option
      v-for="(name, zone) in options"
      :key="zone"
      :value="zone"
    >
      {{ name }}
    </option>
  </Select>
</template>

<script>
import { computed, defineComponent, inject, ref } from 'vue'
import Select from '@/components/forms/Select'

export default defineComponent({
  components: {
    Select,
  },
  props: {
    modelValue: String,
  },
  emits: ['update:modelValue'],

  setup (props, { emit }) {
    const $route = inject('$route')
    const $http = inject('$http')
    const localValue = computed({
      get: () => props.modelValue,
      set: value => emit('update:modelValue', value)
    })
    const options = ref([])

    $http.get($route('timezones')).then(({ data }) => {
      options.value = data
    })

    return {
      localValue,
      options,
    }
  }
})
</script>
