<template>
  <Authenticated>
    <CardWrapper>
      <CardPadding>

        <form @submit.prevent="submit" data-cy="form">
          <Fieldset>
            <InputWrap :error="form.errors.license">
              <Label for="license">{{ __('Product License') }}</Label>
              <Input v-model="form.license" type="text" id="license" required />
            </InputWrap>
            <InputWrap :error="form.errors.name">
              <Label for="name">{{ __('Tenant Name') }}</Label>
              <Input v-model="form.name" type="text" id="name" data-cy="name" required />
            </InputWrap>
            <InputWrap :error="form.errors.ps_url">
              <Label for="ps_url">{{ __('PowerSchool URL') }}</Label>
              <Input v-model="form.ps_url" type="url" id="ps_url" data-cy="ps_url" />
            </InputWrap>
            <InputWrap :error="form.errors.ps_client_id">
              <Label for="ps_client_id">{{ __('PowerSchool Client ID') }}</Label>
              <Input v-model="form.ps_client_id" type="text" id="ps_client_id" data-cy="ps_client_id" required />
            </InputWrap>
            <InputWrap :error="form.errors.ps_secret">
              <Label for="ps_secret">{{ __('PowerSchool Client Secret') }}</Label>
              <Input v-model="form.ps_secret" type="text" id="ps_secret" data-cy="ps_secret" />
            </InputWrap>
            <InputWrap :error="form.errors.allow_password_auth">
              <Label>
                <Checkbox name="allow_password_auth" v-model:checked="form.allow_password_auth" data-cy="allow_password_auth" />
                <CheckboxText>{{ __('Allow password authentication') }}</CheckboxText>
              </Label>
            </InputWrap>
            <InputWrap :error="form.errors.allow_oidc_login">
              <Label>
                <Checkbox name="allow_oidc_login" v-model:checked="form.allow_oidc_login" data-cy="allow_oidc_login" />
                <CheckboxText>{{ __('Allow PowerSchool OpenID Connect authentication') }}</CheckboxText>
              </Label>
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
import { defineComponent, ref, inject } from 'vue'
import { useForm } from '@inertiajs/inertia-vue3'
import Authenticated from '../../layouts/Authenticated'
import Fieldset from '../../components/forms/Fieldset'
import InputWrap from '../../components/forms/InputWrap'
import Label from '../../components/forms/Label'
import Input from '../../components/forms/Input'
import Button from '../../components/Button'
import Checkbox from '../../components/forms/Checkbox'
import CheckboxText from '../../components/forms/CheckboxText'
import CardWrapper from '../../components/CardWrapper'
import CardPadding from '../../components/CardPadding'
import CardAction from '../../components/CardAction'

export default defineComponent({
  components: {
    CardAction,
    CardPadding,
    CardWrapper,
    CheckboxText,
    Checkbox,
    Button,
    Input,
    Label,
    InputWrap,
    Fieldset,
    Authenticated
  },

  props: {
    tenant: Object,
  },

  setup ({ tenant }) {
    const $route = inject('$route')
    const form = useForm({
      ...tenant
    })
    const submit = () => {
      form.post($route('settings.tenant'))
    }

    return {
      form,
      submit,
    }
  },
})
</script>
