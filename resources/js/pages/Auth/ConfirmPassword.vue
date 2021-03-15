<template>
  <div class="mb-4 text-sm text-gray-600">
    This is a secure area of the application. Please confirm your password before continuing.
  </div>

  <breeze-validation-errors class="mb-4" />

  <form @submit.prevent="submit">
    <Fieldset>
      <InputWrap :error="form.errors.password">
        <Label for="password" value="Password">
          {{ __('Password') }}
        </Label>
        <Input id="password" type="password" class="mt-1 block w-full" v-model="form.password" required autocomplete="current-password" autofocus />
      </InputWrap>
    </Fieldset>

    <div class="flex justify-end mt-4">
      <breeze-button class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
        Confirm
      </breeze-button>
    </div>
  </form>
</template>

<script>
import BreezeButton from '@/Components/Button'
import BreezeGuestLayout from "@/Layouts/Guest"
import BreezeInput from '@/Components/Input'
import BreezeValidationErrors from '@/Components/ValidationErrors'
import Label from '@/components/forms/Label'
import Input from '@/components/forms/Input'
import Fieldset from '@/components/forms/Fieldset'
import InputWrap from '@/components/forms/InputWrap'

export default {
  layout: BreezeGuestLayout,

  components: {
    InputWrap,
    Fieldset,
    Input,
    Label,
    BreezeButton,
    BreezeInput,
    BreezeLabel,
    BreezeValidationErrors,
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
