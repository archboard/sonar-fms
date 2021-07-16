<template>
  <Modal
    @close="$emit('close')"
    @action="$emit('save', localContent)"
    ref="modal"
    size="4xl"
  >
    <Wysiwyg v-model="localContent" />
  </Modal>
</template>

<script>
import { defineComponent, ref } from 'vue'
import Modal from '@/components/Modal'
import Wysiwyg from '@/components/forms/Wysiwyg'

export default defineComponent({
  components: {
    Wysiwyg,
    Modal,
  },
  props: {
    content: {
      type: String,
      default: '',
    }
  },
  emits: ['close', 'save'],

  setup (props) {
    const localContent = ref(props.content)

    return {
      localContent,
    }
  }
})
</script>
