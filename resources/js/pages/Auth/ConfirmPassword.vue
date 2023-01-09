<template>
  <div class="mb-4 text-sm text-gray-600">
    This is a secure area of the application. Please confirm your password before continuing.
  </div>

  <ValidationErrors class="mb-4" />

  <form @submit.prevent="submit">
    <Fieldset>
      <InputWrap :error="form.errors.password">
        <Label for="password" value="Password">
          {{ __('Password') }}
        </Label>
        <Input id="password" type="password" v-model="form.password" required autocomplete="current-password" autofocus />
      </InputWrap>
    </Fieldset>

    <div class="flex justify-end mt-4">
      <Button :loading="form.processing">
        Confirm
      </Button>
    </div>
  </form>
</template>

<script>
import Label from '@/components/forms/Label.vue'
import Input from '@/components/forms/Input.vue'
import Fieldset from '@/components/forms/Fieldset.vue'
import InputWrap from '@/components/forms/InputWrap.vue'
import Button from '@/components/Button.vue'
import ValidationErrors from '@/components/ValidationErrors.vue'

export default {
  components: {
    ValidationErrors,
    Button,
    InputWrap,
    Fieldset,
    Input,
    Label,
  },

  data() {
    return {
      form: this.$inertia.form({
        password: '',
      })
    }
  },

  methods: {
    submit() {
      this.form.post(this.route('password.confirm'), {
        onFinish: () => this.form.reset(),
      })
    }
  }
}
</script>
