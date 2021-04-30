<template>
  <li
    :class="[
      active ? 'text-fuchsia-900 bg-fuchsia-100 dark:text-fuchsia-100 bg-fuchsia-600' : 'text-gray-900 dark:text-white',
      'cursor-pointer select-none relative py-2 pl-10 pr-4',
    ]"
  >
    <span
      :class="[
        selected ? 'font-medium' : 'font-normal',
        'block truncate',
      ]"
      >
      <slot />
    </span
    >
    <span
      v-if="selected"
      class="absolute inset-y-0 left-0 flex items-center pl-3 text-fuchsia-600 dark:text-fuchsia-100"
    >
      <CheckIcon class="w-5 h-5" aria-hidden="true" />
    </span>
  </li>
</template>

<script>
import { defineComponent } from 'vue'
import { CheckIcon } from '@heroicons/vue/outline'

export default defineComponent({
  components: {
    CheckIcon,
  },

  props: {
    active: Boolean,
    selected: Boolean,
  }
})
</script>
