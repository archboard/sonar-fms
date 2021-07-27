<template>
  <input
    type="radio"
    class="cursor-pointer focus:ring-primary-500 h-4 w-4 text-primary-600 border-gray-300"
    :value="value"
    v-model="proxyChecked"
    @change="$emit('change', proxyChecked)"
  />
</template>

<script>
import { computed, defineComponent, ref } from 'vue'

export default defineComponent({
  components: {
  },
  emits: [
    'update:checked',
    'change'
  ],

  props: {
    checked: {
      type: [Array, Boolean, String],
      default: false,
    },
    value: {
      default: null,
    },
  },

  setup (props, { emit }) {
    const proxyChecked = computed({
      get: () => props.checked,
      set: value => emit('update:checked', value)
    })

    return {
      proxyChecked,
    }
  }
})
</script>
