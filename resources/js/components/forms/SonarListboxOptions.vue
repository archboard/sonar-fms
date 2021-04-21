<template>
  <ListboxOptions class="absolute w-full py-1 mt-1 overflow-auto text-base bg-white rounded-md shadow-lg max-h-64 ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm">
    <slot/>
  </ListboxOptions>
</template>

<script>
import { defineComponent, ref } from 'vue'
import { ListboxOptions } from '@headlessui/vue'

export default defineComponent({
  components: {
    ListboxOptions,
  }
})
</script>
