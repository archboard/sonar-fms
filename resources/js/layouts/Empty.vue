<template>
  <div>
    <slot />
  </div>
</template>

<script>
import { defineComponent } from 'vue'
import setsTitle from '@/composition/setsTitle'

export default defineComponent({
  components: {
  },

  setup () {
    setsTitle()
  }
})
</script>
