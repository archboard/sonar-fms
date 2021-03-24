<template>
  <select
    v-model="localValue"
    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-primary-500 focus:border-primary-500 rounded-md dark:bg-gray-700 dark:border-gray-900 dark:focus:border-primary-500 transition duration-150 ease-in-out"
  >
    <slot />
  </select>
</template>

<script>
import { defineComponent } from 'vue'

export default defineComponent({
  props: {
    modelValue: {
      type: [String, Number, Boolean],
    }
  },
  emits: ['update:modelValue'],

  computed: {
    localValue: {
      get () {
        return this.modelValue
      },

      set (value) {
        this.$emit('update:modelValue', value)
      }
    }
  }
})
</script>
