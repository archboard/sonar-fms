<template>
  <textarea
    :value="modelValue"
    @input="$event => $emit('update:modelValue', $event.target.value)"
    class="shadow-sm block w-full focus:ring-2 focus:ring-primary-500 focus:ring-offset-primary-500 focus:border-primary-500 sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-900 dark:focus:border-primary-500 transition duration-150 ease-in-out"></textarea>
</template>

<script>
import { defineComponent, ref } from 'vue'

export default defineComponent({
  props: {
    modelValue: String,
  },
  emits: ['update:modelValue']
})
</script>
