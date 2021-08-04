<template>
  <Authenticated>
    <CardWrapper>
      <CardPadding>
        <form @submit.prevent="submit" data-cy="form">
          <Fieldset>
            <InputWrap :error="form.errors.first_name">
              <Label for="first_name">{{ __('First Name') }}</Label>
              <Input v-model="form.first_name" type="text" id="first_name" data-cy="first_name" required />
            </InputWrap>
            <InputWrap :error="form.errors.last_name">
              <Label for="last_name">{{ __('Last Name') }}</Label>
              <Input v-model="form.last_name" type="text" id="last_name" data-cy="last_name" required />
            </InputWrap>
            <InputWrap :error="form.errors.email">
              <Label for="email">{{ __('Email') }}</Label>
              <Input v-model="form.email" type="email" id="email" data-cy="email" required />
            </InputWrap>
            <InputWrap :error="form.errors.timezone">
              <Label for="timezone">{{ __('Timezone') }}</Label>
              <Timezone v-model="form.timezone" id="timezone" data-cy="timezone" required />
            </InputWrap>
            <InputWrap v-if="tenant.allow_password_auth" :error="form.errors.password">
              <Label for="password">{{ __('Password') }}</Label>
              <Input v-model="form.password" type="password" id="password" data-cy="password" />
              <HelpText class="mt-1 ml-1">{{ __('Leave empty to keep your current password.') }}</HelpText>
            </InputWrap>
            <InputWrap v-if="tenant.allow_password_auth" :error="form.errors.password_confirmation">
              <Label for="password_confirmation">{{ __('Confirm Password') }}</Label>
              <Input v-model="form.password_confirmation" type="password" id="password_confirmation" data-cy="password_confirmation" />
            </InputWrap>
          </Fieldset>
          <CardAction :negative-margin="true">
            <Button type="submit" :loading="form.processing">
              {{ __('Save') }}
            </Button>
          </CardAction>
        </form>
      </CardPadding>
    </CardWrapper>
  </Authenticated>
</template>

<script>
import { defineComponent, inject } from 'vue'
import { useForm } from '@inertiajs/inertia-vue3'
import Authenticated from '@/layouts/Authenticated'
import pick from 'lodash/pick'
import Fieldset from '@/components/forms/Fieldset'
import InputWrap from '@/components/forms/InputWrap'
import Label from '@/components/forms/Label'
import Input from '@/components/forms/Input'
import Button from '@/components/Button'
import CardWrapper from '@/components/CardWrapper'
import CardPadding from '@/components/CardPadding'
import HelpText from '@/components/HelpText'
import CardAction from '@/components/CardAction'
import Timezone from '@/components/forms/Timezone'
import PageProps from '@/mixins/PageProps'

export default defineComponent({
  mixins: [PageProps],
  components: {
    Timezone,
    CardAction,
    HelpText,
    CardPadding,
    CardWrapper,
    Button,
    Input,
    Label,
    InputWrap,
    Fieldset,
    Authenticated
  },

  props: {
    user: Object,
  },

  setup ({ user }) {
    const $route = inject('$route')
    const form = useForm({
      ...pick(user, ['first_name', 'last_name', 'email', 'timezone']),
      password: '',
      password_confirmation: '',
    })
    const submit = () => {
      form.post($route('settings.personal'), {
        onFinish () {
          form.reset('password', 'password_confirmation')
        }
      })
    }

    return {
      form,
      submit,
    }
  },
})
</script>
