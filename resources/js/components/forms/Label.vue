<template>
  <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
    <slot/>
    <Req v-if="required" />
  </label>
</template>

<script>
import { defineComponent, ref } from 'vue'
import Req from './Req'

export default defineComponent({
  components: {Req},
  props: {
    required: {
      type: Boolean,
      default: false,
    }
  }
})
</script>
