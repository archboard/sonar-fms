<template>
  <div>
    <slot />
    <FadeIn>
      <Error v-if="message">{{ message }}</Error>
    </FadeIn>
  </div>
</template>
<script>
import Error from '@/components/forms/Error.vue'
import { computed } from 'vue'
import FadeIn from '@/components/transitions/FadeIn.vue'

export default {
  components: {
    FadeIn,
    Error,
  },

  props: {
    error: [String, Array],
  },

  setup (props) {
    const message = computed(() => {
      if (Array.isArray(props.error)) {
        return props.error[0]
      }

      return props.error
    })

    return {
      message,
    }
  },
}
</script>
