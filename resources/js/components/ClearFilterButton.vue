<template>
  <button :class="tables">
    <XIcon class="w-5 h-5 md:w-6 md:h-6 mr-2" />
    <span>{{ __('Clear') }}</span>
  </button>
</template>

<script>
import { defineComponent } from 'vue'
import { XIcon } from '@heroicons/vue/outline'
import buttonClasses from '@/composition/buttonClasses.js'

export default defineComponent({
  components: {
    XIcon,
  },

  setup () {
    const { tables } = buttonClasses()

    return {
      tables,
    }
  }
})
</script>
