<template>
  <Modal
    @close="$emit('close')"
    @action="submitForm"
    :action-text="__('Create')"
    :headline="__('Create a new user')"
    :auto-close="form.processing"
  >
    <form @submit.prevent="submitForm">
      <Fieldset>
        <InputWrap :error="form.errors.first_name">
          <Label :required="true" for="first_name">{{ __('First name') }}</Label>
          <Input v-model="form.first_name" id="first_name" type="text" required />
        </InputWrap>
        <InputWrap :error="form.errors.last_name">
          <Label :required="true" for="last_name">{{ __('Last name') }}</Label>
          <Input v-model="form.last_name" id="last_name" type="text" required />
        </InputWrap>
        <InputWrap :error="form.errors.email">
          <Label :required="true" for="email">{{ __('Email') }}</Label>
          <Input v-model="form.email" id="email" type="email" required />
        </InputWrap>
      </Fieldset>
    </form>
  </Modal>
</template>

<script>
import { defineComponent, inject, ref } from 'vue'
import { useForm } from '@inertiajs/inertia-vue3'
import Modal from '../Modal'
import Fieldset from '../forms/Fieldset'
import InputWrap from '../forms/InputWrap'
import Input from '../forms/Input'
import Req from '../forms/Req'
import Label from '../forms/Label'

export default defineComponent({
  components: {
    Req,
    Input,
    InputWrap,
    Fieldset,
    Modal,
    Label,
  },
  emits: ['close'],

  setup () {
    const $route = inject('$route')
    const form = useForm({
      first_name: '',
      last_name: '',
      email: '',
    })
    const submitForm = () => {
      form.post($route('users.store'), {
        preserveScroll: true
      })
    }

    return {
      form,
      submitForm,
    }
  }
})
</script>
