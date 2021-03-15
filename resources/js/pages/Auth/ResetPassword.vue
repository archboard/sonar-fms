<template>
  <ValidationErrors class="mb-4" />

  <form @submit.prevent="submit">
    <Fieldset>
      <InputWrap :error="form.errors.email">
        <Label for="email">{{ __('Email') }}</Label>
        <Input id="email" type="email" v-model="form.email" required autofocus autocomplete="username" />
      </InputWrap>

      <InputWrap :error="form.errors.password">
        <Label for="password">{{ __('Password') }}</Label>
        <Input id="password" type="password" v-model="form.password" required autocomplete="new-password" />
      </InputWrap>

      <InputWrap>
        <Label for="password_confirmation">{{ __('Confirm Password') }}</Label>
        <Input id="password_confirmation" type="password" v-model="form.password_confirmation" required autocomplete="new-password" />
      </InputWrap>
    </Fieldset>

    <div class="flex items-center justify-end mt-4">
      <Button :loading="form.processing">
        Reset Password
      </Button>
    </div>
  </form>
</template>

<script>
import Fieldset from '@/components/forms/Fieldset'
import InputWrap from '@/components/forms/InputWrap'
import Label from '@/components/forms/Label'
import Input from '@/components/forms/Input'
import Button from '@/components/Button'
import ValidationErrors from '@/components/ValidationErrors'

export default {
  components: {
    ValidationErrors,
    Button,
    Input,
    Label,
    InputWrap,
    Fieldset,
  },

  props: {
    email: String,
    token: String,
  },

  data() {
    return {
      form: this.$inertia.form({
        token: this.token,
        email: this.email,
        password: '',
        password_confirmation: '',
      })
    }
  },

  methods: {
    submit() {
      this.form.post(this.$route('password.update'), {
        onFinish: () => this.form.reset('password', 'password_confirmation'),
      })
    }
  }
}
</script>
