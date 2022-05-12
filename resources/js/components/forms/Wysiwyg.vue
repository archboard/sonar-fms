<template>
  <div class="w-full">
    <ckeditor
      :editor="Editor"
      :config="config"
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
    const config = {
      toolbar: {
        shouldNotGroupWhenFull: true,
        items: [
          'heading',
          '|',
          'bold',
          'italic',
          'link',
          'bulletedList',
          'numberedList',
          '|',
          'outdent',
          'indent',
          '|',
          'imageUpload',
          'blockQuote',
          'insertTable',
          'undo',
          'redo'
        ]
      },
      language: 'en',
      image: {
        toolbar: [
          'imageTextAlternative',
          'imageStyle:inline',
          'imageStyle:block',
          'imageStyle:side'
        ]
      },
      table: {
        contentToolbar: [
          'tableColumn',
          'tableRow',
          'mergeTableCells'
        ]
      }
    }

    return {
      localValue,
      Editor,
      config,
    }
  }
})
</script>
