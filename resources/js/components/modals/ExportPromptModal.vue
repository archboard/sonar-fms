<template>
  <Modal
    @close="$emit('close')"
  >
    <Fieldset>
      <InputWrap :error="form.errors.name">
        <Label required>{{ __('File name') }}</Label>
        <Input v-model="form.name" />
      </InputWrap>
    </Fieldset>
  </Modal>
</template>

<script>
import { defineComponent, ref } from 'vue'
import Modal from '@/components/Modal'
import { useForm } from '@inertiajs/inertia-vue3'
import Fieldset from '@/components/forms/Fieldset'
import InputWrap from '@/components/forms/InputWrap'
import Label from '@/components/forms/Label'
import Input from '@/components/forms/Input'

export default defineComponent({
  components: {
    Input,
    Label,
    InputWrap,
    Fieldset,
    Modal,
  },
  emits: ['close'],
  props: {
    url: {
      type: String,
      required: true,
    },
    filters: {
      type: Object,
      default: () => ({})
    },
  },

  setup () {
    const form = useForm({
      name: '',
    })

    return {
      form,
    }
  }
})
</script>
