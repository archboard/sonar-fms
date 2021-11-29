<template>
  <textarea
    v-model="localValue"
    @input="resize"
    ref="textarea"
    :style="{ height }"
    class="shadow-sm block w-full focus:ring-2 focus:ring-primary-500 focus:ring-offset-primary-500 focus:border-primary-500 sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-900 dark:focus:border-primary-500 transition duration-150 ease-in-out"></textarea>
</template>

<script>
import { defineComponent, ref, onMounted, nextTick } from 'vue'
import hasModelValue from '@/composition/hasModelValue'

export default defineComponent({
  props: {
    modelValue: String,
  },
  emits: ['update:modelValue'],

  setup (props, { emit }) {
    const { localValue } = hasModelValue(props, emit)
    const textarea = ref(null)
    const baseHeight = 66
    const height = ref(`auto`)
    const resize = () => {
      height.value = `auto`

      nextTick(() => {
        const scrollHeight = textarea.value.scrollHeight

        if (scrollHeight > baseHeight) {
          height.value = `${scrollHeight}px`
        }
      })
    }
    onMounted(resize)

    return {
      localValue,
      textarea,
      height,
      resize,
    }
  }
})
</script>
