<template>
  <Modal
    @action="save"
    @close="$emit('close')"
    :auto-close="false"
    :headline="__('Convert invoice to template')"
    :action-loading="templateForm.processing"
    :initial-focus="input"
  >
    <form @submit.prevent="save">
      <HelpText class="mb-4">
        {{ __('This takes all the details of this invoice and converts it into a template that can be reused when creating new invoices.') }}
      </HelpText>
      <Fieldset>
        <InputWrap :error="templateForm.errors.name">
          <Label for="new-template-name" :required="true">{{ __('Name') }}</Label>
          <Input v-model="templateForm.name" ref="input" id="new-template-name" />
        </InputWrap>
      </Fieldset>
    </form>
  </Modal>
</template>

<script>
import { defineComponent, ref } from 'vue'
import Modal from '@/components/Modal.vue'
import { useForm } from '@inertiajs/vue3'
import Fieldset from '@/components/forms/Fieldset.vue'
import Label from '@/components/forms/Label.vue'
import InputWrap from '@/components/forms/InputWrap.vue'
import Input from '@/components/forms/Input.vue'
import ModalHeadline from '@/components/modals/ModalHeadline.vue'
import HelpText from '@/components/HelpText.vue'

export default defineComponent({
  components: {
    HelpText,
    ModalHeadline,
    Input,
    InputWrap,
    Fieldset,
    Modal,
    Label,
  },

  props: {
    endpoint: String,
  },
  emits: ['close'],

  setup (props) {
    const input = ref()
    const templateForm = useForm({
      name: '',
    })
    const save = close => {
      templateForm.post(props.endpoint, {
        preserveScroll: true,
        onSuccess () {
          close()
        }
      })
    }

    return {
      templateForm,
      save,
      input,
    }
  }
})
</script>
