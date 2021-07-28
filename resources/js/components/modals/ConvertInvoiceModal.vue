<template>
  <Modal
    @action="save"
    @close="$emit('close')"
    :auto-close="false"
    :headline="__('Convert invoice to template')"
    :action-loading="templateForm.processing"
    ref="modal"
  >
    <form @submit.prevent="save">
      <HelpText class="mb-4">
        {{ __('This takes all the details of this invoice and converts it into a template that can be reused when creating new invoices.') }}
      </HelpText>
      <Fieldset>
        <InputWrap :error="templateForm.errors.name">
          <Label for="new-template-name" :required="true">{{ __('Name') }}</Label>
          <Input v-model="templateForm.name" id="new-template-name" />
        </InputWrap>
      </Fieldset>
    </form>
  </Modal>
</template>

<script>
import { defineComponent, ref } from 'vue'
import Modal from '@/components/Modal'
import { useForm } from '@inertiajs/inertia-vue3'
import Fieldset from '@/components/forms/Fieldset'
import Label from '@/components/forms/Label'
import InputWrap from '@/components/forms/InputWrap'
import Input from '@/components/forms/Input'
import ModalHeadline from './ModalHeadline'
import HelpText from '@/components/HelpText'

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
    invoice: Object,
    endpoint: String,
  },
  emits: ['close'],

  setup (props) {
    const modal = ref()
    const templateForm = useForm({
      name: '',
    })
    const save = async () => {
      templateForm.post(props.endpoint, {
        preserveScroll: true,
        onSuccess: () => modal.value?.close()
      })
    }

    return {
      templateForm,
      save,
      modal,
    }
  }
})
</script>
