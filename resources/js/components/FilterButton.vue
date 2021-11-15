<template>
  <button class="inline-flex items-center justify-center w-auto bg-white border border-gray-300 dark:border-gray-900 dark:bg-gray-700 rounded-md px-4 shadow focus:outline-none transition hover:ring-2 hover:ring-primary-500 hover:ring-opacity-50 focus:ring-opacity-100 focus:ring-offset-2 dark:focus:ring-offset-gray-800 focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
    <AdjustmentsIcon class="w-6 h-6 mr-2" />
    <span>{{ __('Filters') }}</span>
  </button>
</template>

<script>
import { defineComponent } from 'vue'
import { AdjustmentsIcon } from '@heroicons/vue/outline'

export default defineComponent({
  components: {
    AdjustmentsIcon,
  },
})
</script>
