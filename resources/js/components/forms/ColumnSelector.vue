<template>
  <div class="relative">
    <div
      class="absolute top-0 bottom-0 left-0 flex items-center justify-center px-4"
      @click="iconClicked"
    >
      <LinkIcon class="w-4 h-4 text-gray-500 dark:text-gray-400" />
    </div>
    <select
      v-model="localValue"
      @change="e => $emit('change', e)"
      ref="select"
      class="block w-full px-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-primary-500 focus:border-primary-500 rounded-md dark:bg-gray-700 dark:border-gray-900 dark:focus:border-primary-500 transition duration-150 ease-in-out"
    >
      <option :value="null">{{ __('Do not map') }}</option>
      <option
        v-for="header in headers"
        :key="header"
        :value="header"
      >
        {{ header }}
      </option>
    </select>
  </div>
</template>

<script>
import { computed, defineComponent, ref } from 'vue'
import { LinkIcon } from '@heroicons/vue/outline'

export default defineComponent({
  components: {
    LinkIcon,
  },

  props: {
    headers: Array,
    modelValue: String,
  },
  emits: ['update:modelValue', 'change'],

  setup (props, { emit }) {
    const localValue = computed({
      get: () => props.modelValue,
      set: value => emit('update:modelValue', value)
    })
    const select = ref(null)
    const iconClicked = (e) => {
      select.value.focus()
    }

    return {
      localValue,
      select,
      iconClicked,
    }
  }
})
</script>

