<template>
  <component :is="is" class="transition font-medium text-primary-600 dark:text-primary-500 hover:text-primary-500 dark:hover:text-primary-300 focus:outline-none">
    <slot />
  </component>
</template>

<script>
import { defineComponent } from 'vue'

export default defineComponent({
  props: {
    is: {
      type: String,
      default: 'inertia-link',
    }
  }
})
</script>
