<template>
  <button :class="tables">
    <CloudDownloadIcon class="w-5 h-5 md:w-6 md:h-6 mr-2" />
    <span>{{ __('Export') }}</span>
  </button>
</template>

<script>
import { defineComponent } from 'vue'
import { CloudDownloadIcon } from '@heroicons/vue/outline'
import buttonClasses from '@/composition/buttonClasses.js'

export default defineComponent({
  components: {
    CloudDownloadIcon,
  },

  setup () {
    const { tables } = buttonClasses()

    return {
      tables,
    }
  }
})
</script>
