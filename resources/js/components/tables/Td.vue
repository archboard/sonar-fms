<template>
  <td
    class="px-6 py-4 text-sm"
    :class="{
      'text-gray-500 dark:text-gray-300': lighter,
      'text-gray-900 dark:text-gray-100 font-medium': !lighter,
    }"
  >
    <slot />
  </td>
</template>

<script>
import { defineComponent } from 'vue'

export default defineComponent({
  props: {
    lighter: {
      type: Boolean,
      default: true,
    }
  }
})
</script>
