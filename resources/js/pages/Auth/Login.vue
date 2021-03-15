<template>
  <ValidationErrors class="mb-4" />

  <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
    {{ status }}
  </div>

  <form @submit.prevent="submit">
    <Fieldset>
      <InputWrap>
        <Label for="email">{{ __('Email') }}</Label>
        <Input id="email" type="email" v-model="form.email" required autofocus autocomplete="username" />
      </InputWrap>

      <InputWrap>
        <Label for="password">{{ __('Password') }}</Label>
        <Input id="password" type="password" v-model="form.password" required autocomplete="current-password" />
      </InputWrap>

      <InputWrap>
        <label class="flex items-center">
          <Checkbox name="remember" v-model:checked="form.remember" />
          <span class="ml-2 text-sm text-gray-600">Remember me</span>
        </label>
      </InputWrap>
    </Fieldset>

    <div class="flex items-center justify-end mt-4">
      <InertiaLink v-if="canResetPassword" :href="$route('password.request')" class="underline text-sm text-gray-600 hover:text-gray-900">
        {{ __('Forgot your password ?') }}
      </InertiaLink>

      <Button class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
        {{ __('Log in') }}
      </Button>
    </div>
  </form>
</template>

<script>
import Fieldset from '@/components/forms/Fieldset'
import InputWrap from '@/components/forms/InputWrap'
import Label from '@/components/forms/Label'
import Input from '@/components/forms/Input'
import Checkbox from '@/components/forms/Checkbox'
import Button from '@/components/Button'
import ValidationErrors from '@/components/ValidationErrors'

export default {
  components: {
    ValidationErrors,
    Button,
    Checkbox,
    Input,
    Label,
    InputWrap,
    Fieldset,
  },

  props: {
    canResetPassword: Boolean,
    status: String
  },

  data() {
    return {
      form: this.$inertia.form({
        email: '',
        password: '',
        remember: false
      })
    }
  },

  methods: {
    submit() {
      this.form
        .transform(data => ({
          ...data,
          remember: this.form.remember ? 'on' : ''
        }))
        .post(this.$route('login'), {
          onFinish: () => this.form.reset('password'),
        })
    }
  }
}
</script>
