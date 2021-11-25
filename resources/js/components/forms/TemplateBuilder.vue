<template>
  <div class="relative">
    <div class="absolute top-0 bottom-0 left-0 flex items-center justify-center px-4">
      <CodeIcon class="w-4 h-4 text-gray-500 dark:text-gray-400" />
    </div>
    <Input
      v-model="localValue"
      v-bind="$attrs"
      class="pl-10"
    />
    <div class="absolute top-0 bottom-0 right-0 flex items-center justify-center px-4">
      <button type="button" @click.prevent="showBuilder = true" class="focus:outline-none">
        <CursorClickIcon class="w-5 h-5 text-primary-500 dark:text-primary-400" />
      </button>
    </div>
  </div>

  <TemplateBuilderModal
    v-if="showBuilder"
    @close="showBuilder = false"
    @use-template="useTemplate"
    :content="localValue"
  />
</template>

<script>
import { defineComponent, ref } from 'vue'
import { CodeIcon, CursorClickIcon } from '@heroicons/vue/outline'
import hasModelValue from '@/composition/hasModelValue'
import Input from '@/components/forms/Input'
import TemplateBuilderModal from '@/components/modals/TemplateBuilderModal'

export default defineComponent({
  inheritAttrs: false,
  components: {
    TemplateBuilderModal,
    Input,
    CodeIcon,
    CursorClickIcon,
  },
  props: {
    modelValue: [String, Number],
  },
  emits: ['update:modelValue'],

  setup (props, { emit }) {
    const { localValue } = hasModelValue(props, emit)
    const showBuilder = ref(false)
    const useTemplate = content => {
      localValue.value = content
    }

    return {
      localValue,
      showBuilder,
      useTemplate,
    }
  }
})
</script>
