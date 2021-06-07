<template>
  <div
    class="relative"
    :class="{
      'animate-pulse': pulse,
      'opacity-25': !pulse,
    }"
  >
    <div class="absolute rounded inset-0 bg-gradient-to-br from-gray-300 to-gray-200 dark:from-gray-700 dark:to-gray-600"></div>
    <div class="invisible">
      <slot />
    </div>
  </div>
</template>

<script>
import { defineComponent, ref } from 'vue'

export default defineComponent({
  props: {
    mock: {
      type: Boolean,
      default: false,
    },
    inline: {
      type: Boolean,
      default: false,
    },
    pulse: {
      type: Boolean,
      default: false,
    },
  }
})
</script>
