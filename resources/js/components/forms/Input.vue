<template>
  <input
    v-model="localValue"
    :type="type"
    :class="classes.input"
    ref="input"
  >
</template>

<script>
import { computed, onMounted, ref } from 'vue'
import inputClasses from '@/composition/inputClasses.js'

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
    const classes = inputClasses()
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
      classes,
      input,
      localValue,
    }
  }
}
</script>
