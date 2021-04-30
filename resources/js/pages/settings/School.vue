<template>
  <Authenticated>
    <CardWrapper>
      <CardPadding>
        <form @submit.prevent="submit" data-cy="form">
          <FormMultipartWrapper>
            <div>
              <div class="mb-6">
                <CardSectionHeader>
                  {{ __('Currency Settings') }}
                </CardSectionHeader>
                <HelpText class="text-sm">
                  {{ __('These are the settings that apply to how currency/monetary values are displayed in the system.') }}
                </HelpText>
              </div>

              <Fieldset>
                <InputWrap :error="form.errors.currency_id">
                  <Label for="currency_symbol">{{ __('Currency') }}</Label>
                  <CurrencySelector v-model="form.currency_id" :currencies="currencies" />
                </InputWrap>
              </Fieldset>
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
import pick from 'lodash/pick'
import Fieldset from '../../components/forms/Fieldset'
import InputWrap from '../../components/forms/InputWrap'
import Label from '../../components/forms/Label'
import Input from '../../components/forms/Input'
import Button from '../../components/Button'
import CardWrapper from '../../components/CardWrapper'
import CardPadding from '../../components/CardPadding'
import HelpText from '../../components/HelpText'
import CardAction from '../../components/CardAction'
import FormMultipartWrapper from '../../components/forms/FormMultipartWrapper'
import CardSectionHeader from '../../components/CardSectionHeader'
import Checkbox from '../../components/forms/Checkbox'
import CheckboxText from '../../components/forms/CheckboxText'
import CurrencySelector from '../../components/forms/CurrencySelector'

export default defineComponent({
  components: {
    CurrencySelector,
    CheckboxText,
    Checkbox,
    CardSectionHeader,
    FormMultipartWrapper,
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
    school: Object,
    currencies: Array,
  },

  setup ({ school }) {
    const $route = inject('$route')
    const form = useForm({
      ...pick(school, ['currency_id']),
    })
    const submit = () => {
      form.post($route('settings.school'))
    }

    return {
      form,
      submit,
    }
  },
})
</script>
