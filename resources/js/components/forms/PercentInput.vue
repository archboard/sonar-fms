<template>
  <div class="relative">
    <div class="absolute top-0 bottom-0 left-0 flex items-center justify-center px-4">
      <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="5" x2="5" y2="19"></line><circle cx="6.5" cy="6.5" r="2.5"></circle><circle cx="17.5" cy="17.5" r="2.5"></circle></svg>
    </div>
    <Input
      v-model="localValue"
      v-bind="$attrs"
      class="pl-10"
      type="number"
      step="0.1"
      min="0"
    />
  </div>
</template>

<script>
import { defineComponent } from 'vue'
import hasModelValue from '@/composition/hasModelValue.js'
import Input from '@/components/forms/Input.vue'

export default defineComponent({
  inheritAttrs: false,
  components: {
    Input,
  },
  props: {
    modelValue: [String, Number],
  },
  emits: ['update:modelValue'],

  setup (props, { emit }) {
    const { localValue } = hasModelValue(props, emit)

    return {
      localValue,
    }
  }
})
</script>
