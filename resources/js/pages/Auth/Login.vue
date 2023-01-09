<template>
  <Layout>
    <div>
      <span v-if="tenant.allow_oidc_login" class="rounded-md shadow-sm self-start flex w-full">
        <a href="/auth/powerschool/oidc" class="px-4 py-3 text-base leading-6 flex w-full border border-transparent font-medium rounded-md text-white focus:outline-none transition ease-in-out duration-150 items-center justify-center text-white bg-[#00427c] hover:bg-[#003463] focus:ring focus:ring-[#006bc9] focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition">
          <svg class="w-6 mr-2" viewBox="0 0 20 26" xmlns="http://www.w3.org/2000/svg"><path d="M6.153 0c-.3 0-.542.237-.542.528v24.287H3.886V.528A.535.535 0 003.344 0H.542C.242 0 0 .237 0 .528v24.815c0 .291.243.528.542.528.3 0 .542-.237.542-.528V1.056h1.718v24.287c0 .291.243.528.542.528h2.81c.299 0 .542-.237.542-.528V1.056h2.068c5.75 0 9.768 3.41 9.768 8.292 0 4.7-3.766 8.028-9.225 8.234v-1.587c4.222-.216 7.231-2.953 7.231-6.667 0-3.934-3.196-6.681-7.773-6.681a.55.55 0 00-.384.155.52.52 0 00-.159.373l.003 2.756c0 .291.243.527.543.527 1.717 0 3.45.888 3.45 2.87 0 1.69-1.419 2.87-3.45 2.87-.3 0-.543.236-.543.528 0 .291.243.528.543.528 2.627 0 4.535-1.652 4.535-3.926 0-2.152-1.616-3.7-3.994-3.903l-.001-1.707c3.646.203 6.146 2.453 6.146 5.61 0 3.312-2.75 5.625-6.69 5.625-.299 0-.542.236-.542.528v2.64c0 .29.243.527.543.527 6.288 0 10.851-3.911 10.851-9.3C19.616 3.844 15.154 0 8.765 0H6.153z" fill="currentColor" fill-rule="evenodd"/></svg>
          {{ __('Sign in with PowerSchool') }}
        </a>
      </span>

      <BorderSeparator v-if="tenant.allow_oidc_login && tenant.allow_password_auth" class="my-6">
        {{ __('Or continue with') }}
      </BorderSeparator>
    </div>

    <form v-if="tenant.allow_password_auth" @submit.prevent="submit" data-cy="form">
      <Fieldset>
        <InputWrap :error="form.errors.email">
          <Label for="email" class="text-base">{{ __('Email') }}</Label>
          <Input id="email" type="email" class="text-lg" v-model="form.email" required autofocus autocomplete="username" data-cy="email" />
        </InputWrap>

        <InputWrap>
          <Label for="password" class="text-base">{{ __('Password') }}</Label>
          <Input id="password" type="password" class="text-lg" v-model="form.password" required autocomplete="current-password" data-cy="password" />
        </InputWrap>

        <InputWrap>
          <label class="flex items-center">
            <Checkbox name="remember" v-model:checked="form.remember" />
            <CheckboxText>{{ __('Remember me') }}</CheckboxText>
          </label>
        </InputWrap>
      </Fieldset>

      <div class="my-4">
        <Button :loading="form.processing" :is-block="true" size="lg">
          {{ __('Log in') }}
        </Button>
      </div>

      <p>
        <InertiaLink v-if="canResetPassword" href="/forgot-password" class="hover:underline text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300 transition">
          {{ __('Forgot your password?') }}
        </InertiaLink>
      </p>
    </form>
  </Layout>
</template>

<script>
import Layout from '@/layouts/Guest.vue'
import Fieldset from '@/components/forms/Fieldset.vue'
import InputWrap from '@/components/forms/InputWrap.vue'
import Label from '@/components/forms/Label.vue'
import Input from '@/components/forms/Input.vue'
import Checkbox from '@/components/forms/Checkbox.vue'
import Button from '@/components/Button.vue'
import ValidationErrors from '@/components/ValidationErrors.vue'
import Alert from '@/components/Alert.vue'
import CheckboxText from '@/components/forms/CheckboxText.vue'
import BorderSeparator from '@/components/BorderSeparator.vue'

export default {
  components: {
    BorderSeparator,
    CheckboxText,
    Alert,
    Layout,
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
    status: String,
    tenant: Object,
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
        .post('/login', {
          onFinish: () => this.form.reset('password'),
        })
    }
  }
}
</script>
