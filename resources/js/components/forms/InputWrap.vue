<template>
  <div>
    <slot />
    <Error v-if="message">{{ message }}</Error>
  </div>
</template>
<script>
import Error from '@/components/forms/Error'
import { computed } from 'vue'

export default {
  components: {
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
