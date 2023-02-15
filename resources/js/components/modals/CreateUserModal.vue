<template>
  <Modal
    @close="$emit('close')"
    @action="submitForm"
    :action-text="__('Create')"
    :headline="__('Add a new user')"
    :action-loading="form.processing"
  >
    <HelpText class="mb-4">
      {{ __('Use this form to create a new user. If the email address already exists for a user in a different school, the existing user will be given access to this school.') }}
    </HelpText>
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
import Modal from '@/components/Modal.vue'
import Fieldset from '@/components/forms/Fieldset.vue'
import InputWrap from '@/components/forms/InputWrap.vue'
import Input from '@/components/forms/Input.vue'
import Req from '@/components/forms/Req.vue'
import Label from '@/components/forms/Label.vue'
import HelpText from '@/components/HelpText.vue'

export default defineComponent({
  components: {
    HelpText,
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
