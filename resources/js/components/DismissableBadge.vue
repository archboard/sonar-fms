<template>
  <span class="inline-flex items-center py-0.5 pl-2 pr-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-700 shadow">
    <slot />

    <button type="button" @click.prevent="$emit('dismiss')" class="flex-shrink-0 ml-0.5 h-4 w-4 rounded-full inline-flex items-center justify-center text-primary-400 hover:bg-primary-200 hover:text-primary-500 focus:outline-none focus:bg-primary-500 focus:text-white">
      <span class="sr-only">Remove option</span>
      <svg class="h-2 w-2" stroke="currentColor" fill="none" viewBox="0 0 8 8">
        <path stroke-linecap="round" stroke-width="1.5" d="M1 1l6 6m0-6L1 7" />
      </svg>
    </button>
  </span>
</template>


<script>
import { defineComponent } from 'vue'

export default defineComponent({
  emits: ['dismiss'],
})
</script>
