<template>
  <input
    :type="type"
    :value="modelValue"
    @input="$emit('update:modelValue', $event.target.value)"
    class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md"
  >
</template>

<script>
export default {
  props: {
    modelValue: {
      type: [String, Number],
    },
    type: {
      type: String,
      default: 'text',
    }
  },
  emits: ['update:modelValue'],
}
</script>
