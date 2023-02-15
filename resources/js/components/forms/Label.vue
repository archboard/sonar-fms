<template>
  <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
    <slot/>
    <Req v-if="required" />
  </label>
</template>

<script>
import { defineComponent } from 'vue'
import Req from '@/components/forms/Req.vue'

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
