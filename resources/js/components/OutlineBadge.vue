<template>
  <component :is="is" class="relative inline-flex items-center rounded-full border border-gray-300 dark:border-gray-500 px-3 py-0.5">
    <div class="absolute flex-shrink-0 flex items-center justify-center">
      <span class="h-1.5 w-1.5 rounded-full" :class="computedColor" aria-hidden="true"></span>
    </div>
    <div class="ml-3.5 text-sm font-medium text-gray-900 dark:text-gray-100">
      <slot/>
    </div>
  </component>
</template>

<script>
import { computed, defineComponent } from 'vue'

export default defineComponent({
  props: {
    color: String,
    is: String,
  },

  setup ({ color }) {
    const colors = {
      gray: 'bg-gray-500',
      red: 'bg-red-500',
      green: 'bg-green-500',
      blue: 'bg-blue-500',
      rose: 'bg-rose-500',
      pink: 'bg-pink-500',
      lime: 'bg-lime-500',
      orange: 'bg-orange-500',
    }
    const computedColor = computed(() => colors[color] || color)

    return {
      computedColor,
    }
  }
})
</script>
