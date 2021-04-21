<template>
  <Authenticated>
    <CardWrapper>
      <CardPadding>

        <form @submit.prevent="submit" data-cy="form">
          <FormMultipartWrapper>
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
                  <help-text class="mt-1">{{ __('This will allow users to create and manage a password outside of PowerSchool and allow them to login directly.') }}</help-text>
                </Label>
              </InputWrap>
              <InputWrap :error="form.errors.allow_oidc_login">
                <Label>
                  <Checkbox name="allow_oidc_login" v-model:checked="form.allow_oidc_login" data-cy="allow_oidc_login" />
                  <CheckboxText>{{ __('Allow PowerSchool OpenID Connect authentication') }}</CheckboxText>
                  <help-text class="mt-1">{{ __('This will display a single-sign-on button for PowerSchool and only applies to version 20.11 and newer of PowerSchool.') }}</help-text>
                </Label>
              </InputWrap>
            </Fieldset>

            <div class="pt-8">
              <div class="mb-6">
                <CardSectionHeader>
                  {{ __('SMTP Settings') }}
                </CardSectionHeader>
                <HelpText class="text-sm">
                  {{ __('These are the SMTP settings so that mail can be sent from the system.') }}
                </HelpText>
              </div>

              <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
                <InputWrap :error="form.errors.smtp_host">
                  <Label for="smtp_host">{{ __('Host') }}</Label>
                  <Input v-model="form.smtp_host" type="text" id="smtp_host" data-cy="smtp_host" required />
                </InputWrap>
                <InputWrap :error="form.errors.smtp_port">
                  <Label for="smtp_port">{{ __('Port') }}</Label>
                  <Input v-model="form.smtp_port" type="number" id="smtp_port" data-cy="smtp_port" required />
                </InputWrap>
                <InputWrap :error="form.errors.smtp_username">
                  <Label for="smtp_username">{{ __('Username') }}</Label>
                  <Input v-model="form.smtp_username" type="text" id="smtp_username" data-cy="smtp_username" required />
                </InputWrap>
                <InputWrap :error="form.errors.smtp_password">
                  <Label for="smtp_password">{{ __('Password') }}</Label>
                  <Input v-model="form.smtp_password" class="font-mono" type="text" id="smtp_password" data-cy="smtp_password" required />
                </InputWrap>
                <InputWrap :error="form.errors.smtp_from_name">
                  <Label for="smtp_from_name">{{ __('From Name') }}</Label>
                  <Input v-model="form.smtp_from_name" type="text" id="smtp_from_name" data-cy="smtp_from_name" required />
                </InputWrap>
                <InputWrap :error="form.errors.smtp_from_address">
                  <Label for="smtp_from_address">{{ __('From Address') }}</Label>
                  <Input v-model="form.smtp_from_address" type="email" id="smtp_from_address" data-cy="smtp_from_address" required />
                </InputWrap>
                <InputWrap :error="form.errors.smtp_encryption">
                  <Label for="smtp_encryption">{{ __('Encryption') }}</Label>
                  <Select v-model="form.smtp_encryption" id="smtp_encryption" data-cy="smtp_encryption">
                    <option :value="null">{{ __('None') }}</option>
                    <option value="tls">TLS</option>
                    <option value="ssl">SSL</option>
                  </Select>
                </InputWrap>
              </div>
            </div>
          </FormMultipartWrapper>
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
import CardSectionHeader from '../../components/CardSectionHeader'
import HelpText from '../../components/HelpText'
import Select from '../../components/forms/Select'
import FormMultipartWrapper from '../../components/forms/FormMultipartWrapper'

export default defineComponent({
  components: {
    FormMultipartWrapper,
    Select,
    HelpText,
    CardSectionHeader,
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
