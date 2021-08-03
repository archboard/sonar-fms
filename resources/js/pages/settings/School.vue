<template>
  <Authenticated>
    <div class="space-y-10">
      <CardWrapper>
        <form @submit.prevent="submit" data-cy="form">
          <CardPadding>
            <FormMultipartWrapper>
              <div>
                <div class="mb-6">
                  <CardSectionHeader>
                    {{ __('Currency Settings') }}
                  </CardSectionHeader>
                  <HelpText>
                    {{ __('These are the settings that apply to how currency/monetary values are displayed in the system.') }}
                  </HelpText>
                </div>

                <Fieldset>
                  <InputWrap :error="form.errors.currency_id">
                    <Label for="currency_id" :required="true">{{ __('Currency') }}</Label>
                    <CurrencySelector v-model="form.currency_id" :currencies="currencies" id="currency_id" />
                  </InputWrap>
                </Fieldset>
              </div>
              <div class="pt-8">
                <div class="mb-6">
                  <CardSectionHeader>
                    {{ __('Timezone Settings') }}
                  </CardSectionHeader>
                  <HelpText>
                    {{ __('Set the timezone for your school. This is the timezone used for setting due date and availability.') }}
                  </HelpText>
                </div>

                <Fieldset>
                  <InputWrap :error="form.errors.timezone">
                    <Label for="timezone" :required="true">{{ __('Timezone') }}</Label>
                    <Timezone v-model="form.timezone" id="timezone" />
                  </InputWrap>
                </Fieldset>
              </div>
              <div class="pt-8">
                <div class="mb-6">
                  <CardSectionHeader>
                    {{ __('Tax Settings') }}
                  </CardSectionHeader>
                  <HelpText>
                    {{ __('Enable tax options for invoices and configure default tax settings') }}
                  </HelpText>
                </div>

                <Fieldset>
                  <InputWrap :error="form.errors.collect_tax">
                    <CheckboxWrapper>
                      <Checkbox v-model:checked="form.collect_tax" />
                      <CheckboxText>{{ __('Collect taxes for invoices') }}</CheckboxText>
                    </CheckboxWrapper>
                  </InputWrap>

                  <FadeInGroup>
                    <InputWrap v-if="form.collect_tax" :error="form.errors.tax_rate">
                      <Label for="tax_rate" :required="true">{{ __('Tax rate') }}</Label>
                      <Input v-model="form.tax_rate" id="tax_rate" />
                      <HelpText>
                        {{ __('This is the tax rate collected on invoices. The amount due will reflect this tax rate.') }}
                      </HelpText>
                    </InputWrap>

                    <InputWrap v-if="form.collect_tax" :error="form.errors.tax_label">
                      <Label for="tax_label" :required="true">{{ __('Tax label') }}</Label>
                      <Input v-model="form.tax_label" id="tax_label" placeholder="VAT" />
                    </InputWrap>
                  </FadeInGroup>
                </Fieldset>
              </div>
            </FormMultipartWrapper>
          </CardPadding>
          <CardAction>
            <Button type="submit" :loading="form.processing">
              {{ __('Save') }}
            </Button>
          </CardAction>
        </form>
      </CardWrapper>

      <CardWrapper>
        <CardPadding>
          <CardSectionHeader>
            {{ __('Invoice PDF Layouts') }}
          </CardSectionHeader>
          <HelpText>
            {{ __('Manage the layouts for when an invoice is exported as a PDF file.') }}
          </HelpText>
        </CardPadding>
        <CardAction>
          <Button component="inertia-link" :href="$route('layouts.index')">
            {{ __('Manage layouts') }}
          </Button>
        </CardAction>
      </CardWrapper>

      <CardWrapper>
        <CardPadding>
          <CardSectionHeader>
            {{ __('Payment Methods') }}
          </CardSectionHeader>
          <HelpText>
            {{ __('Manage the available payment methods for your school.') }}
          </HelpText>
        </CardPadding>
        <CardAction>
          <Button component="inertia-link" :href="$route('payment-methods.index')">
            {{ __('Manage payment methods') }}
          </Button>
        </CardAction>
      </CardWrapper>
    </div>
  </Authenticated>
</template>

<script>
import { defineComponent, inject } from 'vue'
import { useForm } from '@inertiajs/inertia-vue3'
import Authenticated from '@/layouts/Authenticated'
import Fieldset from '@/components/forms/Fieldset'
import InputWrap from '@/components/forms/InputWrap'
import Label from '@/components/forms/Label'
import Input from '@/components/forms/Input'
import Button from '@/components/Button'
import CardWrapper from '@/components/CardWrapper'
import CardPadding from '@/components/CardPadding'
import HelpText from '@/components/HelpText'
import CardAction from '@/components/CardAction'
import FormMultipartWrapper from '@/components/forms/FormMultipartWrapper'
import CardSectionHeader from '@/components/CardSectionHeader'
import Checkbox from '@/components/forms/Checkbox'
import CheckboxText from '@/components/forms/CheckboxText'
import CurrencySelector from '@/components/forms/CurrencySelector'
import Timezone from '@/components/forms/Timezone'
import CheckboxWrapper from '@/components/forms/CheckboxWrapper'
import PageProps from '@/mixins/PageProps'
import FadeInGroup from '@/components/transitions/FadeInGroup'

export default defineComponent({
  mixins: [PageProps],
  components: {
    FadeInGroup,
    CheckboxWrapper,
    Timezone,
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
      currency_id: school.currency_id,
      timezone: school.timezone,
      collect_tax: school.collect_tax,
      tax_rate: school.tax_rate_converted,
      tax_label: school.tax_label,
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
