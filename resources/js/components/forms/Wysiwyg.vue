<template>
  <div class="w-full">
    <ckeditor
      :editor="Editor"
      v-model="localValue"
      @ready="editorReady"
    />
  </div>
</template>

<script>
import { computed, defineComponent } from 'vue'
import ckeditor from '@ckeditor/ckeditor5-vue'
import Editor from '@/plugins/ckeditor'

export default defineComponent({
  components: {
    ckeditor: ckeditor.component
  },
  props: {
    modelValue: String,
  },
  emits: ['update:modelValue'],

  setup (props, { emit }) {
    const localValue = computed({
      get: () => props.modelValue,
      set: (value) => emit('update:modelValue', value)
    })

    return {
      localValue,
      Editor,
    }
  }
})
</script>
