<template>
  <div class="relative flex justify-center mt-6">
    <button @click.prevent="$emit('click')" type="button" class="inline-flex items-center shadow-sm px-4 py-1.5 border border-gray-300 dark:border-gray-600 text-sm leading-5 font-medium rounded-full text-gray-700 dark:text-gray-100 bg-white dark:bg-gray-500 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-900">
      <PlusSmIcon class="-ml-1.5 mr-1 h-5 w-5 text-gray-400 dark:text-gray-200" aria-hidden="true" />
      <span>
        <slot />
      </span>
    </button>
  </div>
</template>

<script>
import { defineComponent } from 'vue'
import { PlusSmIcon } from '@heroicons/vue/solid'

export default defineComponent({
  components: {
    PlusSmIcon,
  },
  emits: ['click'],
})
</script>
