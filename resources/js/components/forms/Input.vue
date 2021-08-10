<template>
  <input
    v-model="localValue"
    :type="type"
    class="shadow-sm focus:ring-2 focus:ring-primary-500 focus:ring-offset-primary-500 focus:border-primary-500 block w-full border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-900 dark:focus:border-primary-500 transition duration-150 ease-in-out"
    ref="input"
  >
</template>

<script>
import { computed, onMounted, ref } from 'vue'

export default {
  props: {
    modelValue: {
      type: [String, Number],
    },
    type: {
      type: String,
      default: 'text',
    },
    autofocus: {
      type: Boolean,
      default: false
    }
  },
  emits: ['update:modelValue'],

  setup (props, { emit }) {
    const input = ref(null)
    const localValue = computed({
      get: () => props.modelValue,
      set: state => emit('update:modelValue', state)
    })

    if (props.autofocus) {
      onMounted(() => {
        input.value.focus()
      })
    }

    return {
      input,
      localValue,
    }
  }
}
</script>
