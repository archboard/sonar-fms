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
                    {{ __('Enable tax options for invoices and configure default tax settings.') }}
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
                      <PercentInput v-model="form.tax_rate" id="tax_rate" />
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
              <div class="pt-8">
                <div class="mb-6">
                  <CardSectionHeader>
                    {{ __('Invoice Settings') }}
                  </CardSectionHeader>
                  <HelpText>
                    {{ __('Fine-tune invoice settings.') }}
                  </HelpText>
                </div>

                <Fieldset>
                  <InputWrap :error="form.errors.include_draft_stamp">
                    <CheckboxWrapper>
                      <Checkbox v-model:checked="form.include_draft_stamp" />
                      <CheckboxText>{{ __('Include draft stamp on draft invoices.') }}</CheckboxText>
                    </CheckboxWrapper>
                    <HelpText>
                      {{ __('When enabled there will be a large "DRAFT" indicating that the invoice is incomplete.') }}
                    </HelpText>
                  </InputWrap>

                  <InputWrap :error="form.errors.invoice_number_template">
                    <Label for="invoice_number_template">{{ __('Invoice number prefix') }}</Label>
                    <TemplateBuilder v-model="form.invoice_number_template" placeholder="{year}-" class="font-mono" id="invoice_number_template" />
                    <HelpText class="mb-3">
                      {{ __('Add a prefix to the auto-generated unique invoice number. Use {year} and/or {month} to create a dynamic invoice number based on the current year/month or use any desired static prefix. For example, the prefix "{year}{month}-" would create an invoice number that looks like :number.', { number: `${displayDate(new Date, 'YYYYMM')}-001` }) }}
                    </HelpText>
                    <HelpText>
                      {{ __('Modifying the prefix now will only affect new invoices and will not change previously generated invoice numbers.') }}
                    </HelpText>
                  </InputWrap>

                  <InputWrap :error="form.errors.default_title">
                    <Label for="default_title">{{ __('Default invoice title') }}</Label>
                    <TemplateBuilder v-model="form.default_title" placeholder="{student_number}" class="font-mono" id="default_title" />
                    <HelpText class="mb-3">
                      {{ __('Add a template to use for the default invoice title when creating and importing invoices.') }}
                    </HelpText>
                  </InputWrap>
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
            {{ __('PDF Layouts') }}
          </CardSectionHeader>
          <HelpText>
            {{ __('Manage the layouts for invoices, receipts and account statements as a PDF file.') }}
          </HelpText>
        </CardPadding>
        <CardAction>
          <Button component="inertia-link" href="/layouts/invoices">
            {{ __('Invoice layouts') }}
          </Button>
          <Button component="inertia-link" href="/layouts/receipts">
            {{ __('Receipt layouts') }}
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
          <Button component="inertia-link" href="/payment-methods">
            {{ __('Manage payment methods') }}
          </Button>
        </CardAction>
      </CardWrapper>

      <CardWrapper>
        <CardPadding>
          <CardSectionHeader>
            {{ __('SIS Sync') }}
          </CardSectionHeader>
          <HelpText>
            {{ __('Sync school data from your SIS, including student data. You will receive an email when the sync is finished.') }}
          </HelpText>
        </CardPadding>
        <CardAction>
          <Button
            component="InertiaLink"
            as="button"
            method="post"
            href="/settings/school/sync"
            preserve-scroll
          >
            {{ __('Start sync') }}
          </Button>
        </CardAction>
      </CardWrapper>
    </div>
  </Authenticated>
</template>

<script>
import { defineComponent } from 'vue'
import { useForm } from '@inertiajs/inertia-vue3'
import Authenticated from '@/layouts/Authenticated.vue'
import Fieldset from '@/components/forms/Fieldset.vue'
import InputWrap from '@/components/forms/InputWrap.vue'
import Label from '@/components/forms/Label.vue'
import Input from '@/components/forms/Input.vue'
import Button from '@/components/Button.vue'
import CardWrapper from '@/components/CardWrapper.vue'
import CardPadding from '@/components/CardPadding.vue'
import HelpText from '@/components/HelpText.vue'
import CardAction from '@/components/CardAction.vue'
import FormMultipartWrapper from '@/components/forms/FormMultipartWrapper.vue'
import CardSectionHeader from '@/components/CardSectionHeader.vue'
import Checkbox from '@/components/forms/Checkbox.vue'
import CheckboxText from '@/components/forms/CheckboxText.vue'
import CurrencySelector from '@/components/forms/CurrencySelector.vue'
import Timezone from '@/components/forms/Timezone.vue'
import CheckboxWrapper from '@/components/forms/CheckboxWrapper.vue'
import PageProps from '@/mixins/PageProps'
import FadeInGroup from '@/components/transitions/FadeInGroup.vue'
import displaysDate from '@/composition/displaysDate.js'
import PercentInput from '@/components/forms/PercentInput.vue'
import TemplateBuilder from '@/components/forms/TemplateBuilder.vue'

export default defineComponent({
  mixins: [PageProps],
  components: {
    TemplateBuilder,
    PercentInput,
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
    const form = useForm({
      currency_id: school.currency_id,
      timezone: school.timezone,
      collect_tax: school.collect_tax,
      include_draft_stamp: school.include_draft_stamp,
      tax_rate: school.tax_rate_converted,
      tax_label: school.tax_label,
      invoice_number_template: school.invoice_number_template,
      default_title: school.default_title,
    })
    const submit = () => {
      form.post('/settings/school')
    }
    const { displayDate } = displaysDate()

    return {
      form,
      submit,
      displayDate,
    }
  },
})
</script>
