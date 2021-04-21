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
                <InputWrap :error="form.errors.currency_symbol">
                  <Label for="currency_symbol">{{ __('Currency Symbol') }}</Label>
                  <Input v-model="form.currency_symbol" type="text" id="currency_symbol" data-cy="currency_symbol" required />
                </InputWrap>
                <InputWrap :error="form.errors.currency_decimals">
                  <Label for="currency_decimals">{{ __('Currency Decimal Places') }}</Label>
                  <Input v-model="form.currency_decimals" type="number" id="currency_decimals" data-cy="currency_decimals" required />
                  <help-text class="mt-1">{{ __('This is the number of decimal places to add or round to when currencies are displayed.') }}</help-text>
                </InputWrap>
<!--                <InputWrap :error="form.errors.use_thousands_separator">-->
<!--                  <label>-->
<!--                    <Checkbox v-model:checked="form.use_thousands_separator" name="use_thousands_separator" data-cy="use_thousands_separator" />-->
<!--                    <CheckboxText>{{ __('Use thousands separator (,) when') }}</CheckboxText>-->
<!--                  </label>-->
<!--                </InputWrap>-->
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

export default defineComponent({
  components: {
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
  },

  setup ({ school }) {
    const $route = inject('$route')
    const form = useForm({
      ...pick(school, ['currency_decimals', 'currency_symbol']),
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
