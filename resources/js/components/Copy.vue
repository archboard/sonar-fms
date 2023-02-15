<template>
  <span @click.prevent="copy" class="relative cursor-pointer">
    <slot />
    <FadeIn>
      <div v-if="copied" class="absolute inset-0 text-xs flex items-center justify-center backdrop-blur-xs rounded-md">
        <span>{{ __('Copied!') }}</span>
      </div>
    </FadeIn>
  </span>
</template>

<script>
import { defineComponent, ref } from 'vue'
import ultralightCopy from 'copy-to-clipboard-ultralight'
import FadeIn from '@/components/transitions/FadeIn.vue'

export default defineComponent({
  components: {FadeIn},
  props: {
    copyValue: {
      type: String,
      required: true,
    }
  },

  setup (props) {
    const copied = ref(false)
    const copy = () => {
      if (ultralightCopy(props.copyValue)) {
        copied.value = true

        setTimeout(() => {
          copied.value = false
        }, 2000)
      }
    }

    return {
      copy,
      copied,
    }
  }
})
</script>
