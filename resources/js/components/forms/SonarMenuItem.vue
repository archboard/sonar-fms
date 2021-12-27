<template>
  <MenuItem v-slot="{ active }">
    <component
      :is="is"
      :class="[
        active ? 'bg-gradient-to-bl from-fuchsia-500 to-fuchsia-600 dark:from-fuchsia-600 dark:to-fuchsia-700 text-white' : 'text-gray-900 dark:text-gray-100',
        'group flex rounded-md items-center w-full px-2 py-2 text-sm cursor-pointer focus:outline-none focus:ring-1 focus:ring-gray-300 dark:focus:ring-gray-500',
      ]"
      v-bind="$attrs"
    >
      <slot :active="active" />
    </component>
  </MenuItem>
</template>

<script>
import { defineComponent } from 'vue'
import { MenuItem } from '@headlessui/vue'

export default defineComponent({
  inheritAttrs: false,
  components: {
    MenuItem,
  },

  props: {
    is: {
      type: String,
      default: 'button',
    }
  },
})
</script>
