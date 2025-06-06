<template>
  <Authenticated>
    <template #actions>
      <Button size="sm" @click.prevent="showTemplates = true" color="white">
        {{ __('View templates') }}
      </Button>
      <Button component="inertia-link" :href="`/payments/imports/${paymentImport.id}/edit`" size="sm">
        {{ __('Edit') }}
      </Button>
    </template>

    <CardWrapper class="mb-8">
      <CardPadding>
        <div class="leading-6">
          <p>{{ __('This is where you map the columns of your spreadsheet to individual fields for the payments in each row. You also have the ability to set a field value manually and it will apply to every payment generated by this import.') }}</p>
        </div>
      </CardPadding>
    </CardWrapper>

    <form @submit.prevent="save">
      <Alert v-if="form.hasErrors" level="error" class="mb-8">
        {{ __('Please correct the errors below and try again.') }}
      </Alert>

      <Fieldset>
        <InputWrap :error="form.errors.invoice_column">
          <Label for="invoice_column">{{ __('Invoice number column') }} <Req /></Label>
          <ColumnSelector v-model="form.invoice_column" :headers="headers" id="invoice_column" :required="true" />
          <HelpText>
            {{ __('This is the column that contains the invoice number to which the payment will be applied.') }}
          </HelpText>
        </InputWrap>

        <InputWrap>
          <Label for="invoice_payment_term">{{ __('Payment term') }}</Label>
          <MapField v-model="form.invoice_payment_term" :headers="headers" id="invoice_payment_term">
            <Input v-model="form.invoice_payment_term.value" id="invoice_payment_term" />
            <template #after>
              <HelpText>
                {{ __("If the row's payment details should be associated with a term for that invoice, you can use the format [payment term]/[total payments for schedule]. For example, if there is a payment schedule that has 3 terms and the payment applies to the first payment, the value should be 1/3. It will also be assumed that the invoice is using that payment schedule and will be updated to use that payment schedule when doing calculations.") }}
              </HelpText>
            </template>
          </MapField>
        </InputWrap>

        <InputWrap>
          <Label for="payment_method">{{ __('Payment method') }}</Label>
          <MapField v-model="form.payment_method" :headers="headers" id="payment_method">
            <PaymentMethodSelector v-model="form.payment_method.value" id="payment_method" />
            <template #after>
              <HelpText>
                <HelpText>{{ __("The import can detect different values for the different payment methods, which is configurable.") }} <Link href="/payment-methods">{{ __("Manage payment methods") }}.</Link></HelpText>
              </HelpText>
            </template>
          </MapField>
        </InputWrap>

        <InputWrap>
          <Label for="transaction_details">{{ __('Transaction details') }}</Label>
          <MapField v-model="form.transaction_details" :headers="headers" id="invoice_payment_term">
            <Input v-model="form.transaction_details.value" id="transaction_details" />
            <template #after>
              <HelpText>{{ __("This could hold additional information about the payment, such as a transaction number, to add more auditable details about the payment.") }}</HelpText>
            </template>
          </MapField>
        </InputWrap>

        <InputWrap :error="form.errors.paid_at">
          <Label for="paid_at">{{ __('Date paid') }} <Req /></Label>
          <MapField v-model="form.paid_at" id="paid_at" :headers="headers" :required="true">
            <DatePicker v-model="form.paid_at.value" mode="date" id="paid_at" />
          </MapField>
        </InputWrap>

        <InputWrap :error="form.errors.amount">
          <Label for="amount">{{ __('Amount') }} <Req /></Label>
          <MapField v-model="form.amount" id="amount" :headers="headers" :required="true">
            <CurrencyInput v-model="form.amount.value" id="amount" />
          </MapField>
        </InputWrap>

        <InputWrap :error="form.errors.made_by">
          <Label for="made_by">{{ __('Paid by') }}</Label>
          <MapField v-model="form.made_by" id="made_by" :headers="headers">
            <UserTypeahead v-model="form.made_by.value" id="made_by" />
            <template #after>
              <HelpText>{{ __('The value of this column should be an email address.') }}</HelpText>
            </template>
          </MapField>
        </InputWrap>

        <InputWrap>
          <Label for="notes">{{ __('Notes') }}</Label>
          <MapField v-model="form.notes" id="notes" :headers="headers">
            <Textarea v-model="form.notes.value" id="notes" />
          </MapField>
        </InputWrap>
      </Fieldset>

      <div class="mt-8 p-4 border-t border-gray-400 bg-white dark:bg-gray-700 dark:border-gray-200 rounded-b-md">
        <Button type="submit" :loading="form.processing">
          {{ __('Save mapping') }}
        </Button>
      </div>
    </form>

    <InvoiceTemplatesModal
      v-if="showTemplates"
      @use="useTemplate"
      @close="showTemplates = false"
      route-base="/payments/imports/templates"
      :invoice="form"
    />
  </Authenticated>
</template>

<script>
import { defineComponent, ref } from 'vue'
import Authenticated from '@/layouts/Authenticated.vue'
import Button from '@/components/Button.vue'
import CardWrapper from '@/components/CardWrapper.vue'
import CardPadding from '@/components/CardPadding.vue'
import { useForm } from '@inertiajs/vue3'
import Alert from '@/components/Alert.vue'
import InputWrap from '@/components/forms/InputWrap.vue'
import Label from '@/components/forms/Label.vue'
import MapField from '@/components/forms/MapField.vue'
import ColumnSelector from '@/components/forms/ColumnSelector.vue'
import Req from '@/components/forms/Req.vue'
import HelpText from '@/components/HelpText.vue'
import Input from '@/components/forms/Input.vue'
import invoiceImportMapField from '@/composition/invoiceImportMapField.js'
import Fieldset from '@/components/forms/Fieldset.vue'
import PaymentMethodSelector from '@/components/forms/PaymentMethodSelector.vue'
import DatePicker from '@/components/forms/DatePicker.vue'
import CurrencyInput from '@/components/forms/CurrencyInput.vue'
import UserTypeahead from '@/components/forms/UserTypeahead.vue'
import Link from '@/components/Link.vue'
import Textarea from '@/components/forms/Textarea.vue'
import InvoiceTemplatesModal from '@/components/modals/InvoiceTemplatesModal.vue'
import cloneDeep from 'lodash/cloneDeep'

export default defineComponent({
  components: {
    InvoiceTemplatesModal,
    Textarea,
    PaymentMethodSelector,
    Fieldset,
    Input,
    HelpText,
    Req,
    InputWrap,
    Alert,
    CardPadding,
    CardWrapper,
    Button,
    Authenticated,
    Label,
    MapField,
    ColumnSelector,
    Link,
    DatePicker,
    CurrencyInput,
    UserTypeahead,
  },
  props: {
    paymentImport: Object,
    headers: Array,
  },

  setup (props) {
    const showTemplates = ref(false)
    const { addMapFieldValue } = invoiceImportMapField()
    const form = useForm({
      invoice_column: props.paymentImport.mapping?.invoice_column || null,
      invoice_payment_term: props.paymentImport.mapping?.invoice_payment_term || addMapFieldValue(),
      payment_method: props.paymentImport.mapping?.payment_method || addMapFieldValue(),
      transaction_details: props.paymentImport.mapping?.transaction_details || addMapFieldValue(),
      paid_at: props.paymentImport.mapping?.paid_at || addMapFieldValue(),
      amount: props.paymentImport.mapping?.amount || addMapFieldValue(),
      made_by: props.paymentImport.mapping?.made_by || addMapFieldValue({}),
      notes: props.paymentImport.mapping?.notes || addMapFieldValue(),
    })
    const save = () => {
      form.put(`/payments/imports/${props.paymentImport.id}/map`)
    }
    const useTemplate = template => {
      Object.keys(template.template).forEach(key => {
        if (typeof form[key] !== 'undefined') {
          form[key] = cloneDeep(template.template[key])
        }
      })
    }

    return {
      showTemplates,
      save,
      form,
      useTemplate,
    }
  }
})
</script>
