<template>
  <component :is="is" class="relative inline-flex items-center rounded-full border border-gray-300 dark:border-gray-500 px-3 py-0.5">
    <div class="absolute flex-shrink-0 flex items-center justify-center">
      <span class="h-1.5 w-1.5 rounded-full" :class="color" aria-hidden="true"></span>
    </div>
    <div class="ml-3.5 text-sm font-medium text-gray-900 dark:text-gray-100">
      <slot/>
    </div>
  </component>
</template>

<script>
import { defineComponent } from 'vue'

export default defineComponent({
  props: {
    color: String,
    is: String,
  },
})
</script>
