<template>
  <button :class="tables">
    <AdjustmentsIcon class="w-5 h-5 md:w-6 md:h-6 mr-2" />
    <span>{{ __('Filters') }}</span>
  </button>
</template>

<script>
import { defineComponent } from 'vue'
import { AdjustmentsIcon } from '@heroicons/vue/outline'
import buttonClasses from '@/composition/buttonClasses.js'

export default defineComponent({
  components: {
    AdjustmentsIcon,
  },

  setup () {
    const { tables } = buttonClasses()

    return {
      tables,
    }
  }
})
</script>
