<template>
  <div class="mb-4 text-sm text-gray-600">
    Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.
  </div>

  <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
    {{ status }}
  </div>

  <ValidationErrors class="mb-4" />

  <form @submit.prevent="submit">
    <Fieldset>
      <InputWrap :error="form.errors.email">
        <Label for="email">{{ __('Email') }}</Label>
        <Input id="email" type="email" v-model="form.email" required autofocus autocomplete="username" />
      </InputWrap>
    </Fieldset>

    <div class="flex items-center justify-end mt-4">
      <Button :loading="form.processing">
        Email Password Reset Link
      </Button>
    </div>
  </form>
</template>

<script>
import Button from '@/components/Button'
import ValidationErrors from '@/components/ValidationErrors'
import Fieldset from '@/components/forms/Fieldset'
import InputWrap from '@/components/forms/InputWrap'
import Label from '@/components/forms/Label'
import Input from '@/components/forms/Input'

export default {
  components: {
    Input,
    Label,
    InputWrap,
    Fieldset,
    ValidationErrors,
    Button,
  },

  props: {
    status: String
  },

  data() {
    return {
      form: this.$inertia.form({
        email: ''
      })
    }
  },

  methods: {
    submit() {
      this.form.post(this.$route('password.email'))
    }
  }
}
</script>
