<template>
  <ListboxButton class="relative w-full py-2 pl-3 pr-10 text-left bg-white dark:bg-gray-700 dark:text-white border border-gray-300 dark:border-gray-900 rounded-lg cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-opacity-75 focus-visible:ring-white focus-visible:ring-offset-fuchsia-300 focus-visible:ring-offset-2 focus-visible:border-primary-500">
    <slot/>
  </ListboxButton>
</template>

<script>
import { defineComponent, ref } from 'vue'
import { ListboxButton } from '@headlessui/vue'

export default defineComponent({
  components: {
    ListboxButton,
  }
})
</script>
