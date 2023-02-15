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

            <InputWrap :error="form.errors.refunded_at">
              <Label for="refunded_at" required>{{ __('Refund date') }}</Label>
              <DatePicker v-model="form.refunded_at" mode="date" id="refunded_at" />
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
import Authenticated from '@/layouts/Authenticated.vue'
import { useForm } from '@inertiajs/vue3'
import Fieldset from '@/components/forms/Fieldset.vue'
import InputWrap from '@/components/forms/InputWrap.vue'
import CardWrapper from '@/components/CardWrapper.vue'
import CardPadding from '@/components/CardPadding.vue'
import CardAction from '@/components/CardAction.vue'
import Button from '@/components/Button.vue'
import Label from '@/components/forms/Label.vue'
import CurrencyInput from '@/components/forms/CurrencyInput.vue'
import DatePicker from '@/components/forms/DatePicker.vue'
import Input from '@/components/forms/Input.vue'
import Textarea from '@/components/forms/Textarea.vue'
import HelpText from '@/components/HelpText.vue'
import Alert from '@/components/Alert.vue'

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
    DatePicker,
  },
  props: {
    invoice: Object,
  },

  setup ({ invoice }) {
    const form = useForm({
      amount: null,
      refunded_at: new Date(),
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
