<template>
  <Modal
    @close="$emit('close')"
    @action="save"
    ref="modal"
    size="2xl"
    :auto-close="false"
    :action-loading="form.processing"
    :headline="__('Edit comment')"
  >
    <div class="mt-4">
      <label :for="`comment-${comment.id}`" class="sr-only">Comment</label>
      <Textarea v-model="form.comment" rows="3" :id="`comment-${comment.id}`" />
      <Error v-if="form.errors.comment">{{ form.errors.comment }}</Error>
      <HelpText>{{ __("You can use Markdown to format your comments.") }}</HelpText>
    </div>
  </Modal>
</template>

<script>
import { defineComponent } from 'vue'
import Modal from '@/components/Modal.vue'
import Textarea from '@/components/forms/Textarea.vue'
import Error from '@/components/forms/Error.vue'
import { useForm } from '@inertiajs/inertia-vue3'
import HelpText from '@/components/HelpText.vue'

export default defineComponent({
  components: {
    HelpText,
    Textarea,
    Error,
    Modal,
  },
  props: {
    comment: {
      type: Object,
      default: () => ({}),
    },
    endpoint: String,
    method: {
      type: String,
      default: 'put'
    },
  },
  emits: ['close'],

  setup (props) {
    const form = useForm({
      comment: props.comment.comment,
    })
    const save = close => {
      form[props.method](props.endpoint, {
        preserveScroll: true,
        onSuccess () {
          close()
        }
      })
    }

    return {
      form,
      save,
    }
  }
})
</script>
