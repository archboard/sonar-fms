<template>
  <Layout>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-300">
      {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

    <ValidationErrors class="mb-4" />

    <form @submit.prevent="submit">
      <Fieldset>
        <InputWrap :error="form.errors.email">
          <Label for="email" class="text-base">{{ __('Email') }}</Label>
          <Input id="email" type="email" class="text-lg" v-model="form.email" required autofocus autocomplete="username" />
        </InputWrap>
      </Fieldset>

      <div class="mt-6">
        <Button :loading="form.processing" size="lg" :is-block="true">
          {{ __('Email Password Reset Link') }}
        </Button>
      </div>
    </form>
  </Layout>
</template>

<script>
import Button from '@/components/Button.vue'
import ValidationErrors from '@/components/ValidationErrors.vue'
import Fieldset from '@/components/forms/Fieldset.vue'
import InputWrap from '@/components/forms/InputWrap.vue'
import Label from '@/components/forms/Label.vue'
import Input from '@/components/forms/Input.vue'
import Layout from '@/layouts/Guest.vue'
import Alert from '@/components/Alert'

export default {
  components: {
    Alert,
    Layout,
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
