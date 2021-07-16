<template>
  <div class="w-full">
    <ckeditor
      :editor="InlineEditor"
      v-model="localValue"
      :config="config"
      @ready="editorReady"
    />
  </div>
</template>

<script>
import { computed, defineComponent, ref } from 'vue'
import { component } from '@ckeditor/ckeditor5-vue'
// import ClassicEditor from '@ckeditor/ckeditor5-build-classic'
import InlineEditor from '@/plugins/ckeditor'

export default defineComponent({
  components: {
    ckeditor: component
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
        items: [
          'undo',
          'redo',
          '|',
          'heading',
          '|',
          'bold',
          'italic',
          'underline',
          // 'strikethrough',
          // '|',
          // 'fontColor',
          'fontSize',
          // 'fontFamily',
          // 'highlight',
          '|',
          'alignment',
          'bulletedList',
          'numberedList',
          'link',
          '|',
          'outdent',
          'indent',
          '|',
          // '-',
          'imageInsert',
          // 'insertTable',
          // '|',
          // 'pageBreak',
          // 'horizontalLine',
          // 'sourceEditing',
        ],
        shouldNotGroupWhenFull: true
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
      },
    }
    const editorReady = e => {
      console.log('wysiwyg editor')
    }

    return {
      localValue,
      // ClassicEditor,
      InlineEditor,
      config,
      editorReady,
    }
  }
})
</script>
