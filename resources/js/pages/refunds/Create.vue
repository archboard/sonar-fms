<template>
  <Authenticated>
    <Alert v-if="invoice.parent" level="warning" class="mb-4">
      {{ __('This invoice is part of the combined invoice :invoice. The refund will be recorded for this invoice, but will also be reflected in the combined invoice.', { invoice: `${invoice.parent.title} (${invoice.parent.invoice_number})` }) }}
    </Alert>

    <form @submit.prevent="save">
      <CardWrapper>
        <CardPadding>
          <Fieldset>
            <InputWrap :error="form.errors.amount">
              <Label for="amount" required>{{ __('Refund amount') }}</Label>
              <CurrencyInput v-model="form.amount" id="amount" />
              <HelpText>{{ __('The amount should be less than or equal to :amount.', { amount: invoice.total_paid_formatted }) }}</HelpText>
            </InputWrap>

            <InputWrap :error="form.errors.transaction_details">
              <Label for="transaction_details">{{ __('Transaction details') }}</Label>
              <Input v-model="form.transaction_details" id="transaction_details" />
              <HelpText>{{ __("This could hold additional information about the payment, such as a transaction number, to add more auditable details about the payment.") }}</HelpText>
            </InputWrap>

            <InputWrap :error="form.errors.notes">
              <Label for="notes">{{ __('Notes') }}</Label>
              <Textarea v-model="form.notes" id="notes" />
              <HelpText>{{ __('Optional additional internal notes. Only other administrators can view these notes.') }}</HelpText>
            </InputWrap>
          </Fieldset>
        </CardPadding>
        <CardAction>
          <Button type="submit" :loading="form.processing">{{ __('Save') }}</Button>
          <Button :href="`/invoices/${invoice.uuid}`" component="InertiaLink" color="white">{{ __('Cancel') }}</Button>
        </CardAction>
      </CardWrapper>
    </form>
  </Authenticated>
</template>

<script>
import { defineComponent, ref } from 'vue'
import Authenticated from '@/layouts/Authenticated'
import { useForm } from '@inertiajs/inertia-vue3'
import Fieldset from '@/components/forms/Fieldset'
import InputWrap from '@/components/forms/InputWrap'
import CardWrapper from '@/components/CardWrapper'
import CardPadding from '@/components/CardPadding'
import CardAction from '@/components/CardAction'
import Button from '@/components/Button'
import Label from '@/components/forms/Label'
import CurrencyInput from '@/components/forms/CurrencyInput'
import Input from '@/components/forms/Input'
import Textarea from '@/components/forms/Textarea'
import HelpText from '@/components/HelpText'
import Alert from '@/components/Alert'

export default defineComponent({
  components: {
    Alert,
    HelpText,
    Textarea,
    Input,
    CurrencyInput,
    Button,
    CardAction,
    CardPadding,
    CardWrapper,
    InputWrap,
    Fieldset,
    Authenticated,
    Label,
  },
  props: {
    invoice: Object,
  },

  setup ({ invoice }) {
    const form = useForm({
      amount: null,
      transaction_details: null,
      notes: null,
    })
    const save = () => {
      form.post(`/invoices/${invoice.uuid}/refunds`)
    }

    return {
      form,
      save,
    }
  }
})
</script>
