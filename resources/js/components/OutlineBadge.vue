<template>
  <component
    :is="is"
    class="relative inline-flex items-center rounded-full border border-gray-300 dark:border-gray-500 px-3 py-0.5 bg-white bg-gray-900"
    :class="{
      'pr-1': showDismiss,
    }"
  >
    <div class="absolute flex-shrink-0 flex items-center justify-center">
      <ColorMenu v-if="editColor" v-model="localColor">
        <span class="h-2 w-2 rounded-full" :class="computedColor" aria-hidden="true"></span>
      </ColorMenu>
      <span v-else class="h-2 w-2 rounded-full" :class="computedColor" aria-hidden="true"></span>
    </div>
    <div class="ml-3.5 flex-1 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
      <slot/>
    </div>
    <button v-if="showDismiss" type="button" @click.prevent="$emit('dismiss')" class="flex-shrink-0 ml-1 h-4 w-4 rounded-full inline-flex items-center justify-center text-gray-400 hover:bg-red-200 hover:text-red-500 focus:outline-none focus:bg-red-500 focus:text-white">
      <span class="sr-only">Remove option</span>
      <svg class="h-2 w-2" stroke="currentColor" fill="none" viewBox="0 0 8 8">
        <path stroke-linecap="round" stroke-width="1.5" d="M1 1l6 6m0-6L1 7" />
      </svg>
    </button>
  </component>
</template>

<script>
import { computed, defineComponent, ref } from 'vue'
import tagColorKey from '@/composition/tagColorKey'
import ColorMenu from '@/components/ColorMenu'

export default defineComponent({
  components: {
    ColorMenu,
  },
  props: {
    color: String,
    showDismiss: {
      type: Boolean,
      default: () => false,
    },
    editColor: {
      type: Boolean,
      default: () => false,
    },
    is: {
      type: String,
      default: 'span',
    },
  },
  emits: ['dismiss', 'update:color'],

  setup (props, { emit }) {
    const localColor = computed({
      get: () => props.color,
      set: value => emit('update:color', value)
    })
    const colors = tagColorKey()
    const computedColor = computed(() => colors[localColor.value] || localColor.value)

    return {
      computedColor,
      localColor,
    }
  }
})
</script>
