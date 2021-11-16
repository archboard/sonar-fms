<template>
  <Authenticated>
    <form @submit.prevent="save">
      <CardWrapper>
        <CardPadding>
          <Fieldset>
            <InputWrap :error="form.errors.invoice_uuid">
              <Label for="invoice_uuid">{{ __('Invoice') }} <Req /></Label>
              <InvoiceTypeahead v-model="selectedInvoice" id="invoice_uuid" />
              <HelpText>{{ __('This is the invoice for which you are recording a payment.') }}</HelpText>
            </InputWrap>

            <InputWrap :error="form.errors.payment_method_id">
              <Label for="payment_method_id">{{ __('Payment method') }}</Label>
              <Select v-model="form.payment_method_id" id="payment_method_id">
                <option :value="null">{{ __('N/A') }}</option>
                <option v-for="method in paymentMethods" :key="method.id" :value="method.id">{{ method.name }}</option>
              </Select>
              <HelpText>{{ __("Associating a payment method isn't required, but could be helpful for record keeping.") }} <Link href="/payment-methods">{{ __("Manage payment methods") }}.</Link></HelpText>
            </InputWrap>

            <InputWrap :error="form.errors.paid_at">
              <Label for="paid_at">{{ __('Date paid') }} <Req /></Label>
              <DatePicker v-model="form.paid_at" mode="date" id="paid_at" />
            </InputWrap>

            <InputWrap :error="form.errors.amount">
              <Label for="amount">{{ __('Amount') }} <Req /></Label>
              <CurrencyInput v-model="form.amount" id="amount" />
            </InputWrap>

            <InputWrap :error="form.errors.made_by">
              <Label for="made_by">{{ __('Paid by') }}</Label>
              <UserTypeahead v-model="selectedUser" id="made_by" />
              <HelpText>{{ __('Associating a payment to a user is helpful for historical records.') }}</HelpText>
            </InputWrap>
          </Fieldset>
        </CardPadding>
        <CardAction>
          <Button type="submit" :loading="form.processing">
            {{ __('Save') }}
          </Button>
          <Button is="InertiaLink" href="/payments" color="white">
            {{ __('Cancel') }}
          </Button>
        </CardAction>
      </CardWrapper>
    </form>
  </Authenticated>
</template>

<script>
import { defineComponent, ref } from 'vue'
import Authenticated from '@/layouts/Authenticated'
import { useForm } from '@inertiajs/inertia-vue3'
import CardWrapper from '@/components/CardWrapper'
import CardPadding from '@/components/CardPadding'
import CardAction from '@/components/CardAction'
import Button from '@/components/Button'
import Fieldset from '@/components/forms/Fieldset'
import InputWrap from '@/components/forms/InputWrap'
import Label from '@/components/forms/Label'
import DatePicker from '@/components/forms/DatePicker'
import InvoiceTypeahead from '@/components/forms/InvoiceTypeahead'
import Select from '@/components/forms/Select'
import HelpText from '@/components/HelpText'
import Link from '@/components/Link'
import Req from '@/components/forms/Req'
import CurrencyInput from '@/components/forms/CurrencyInput'
import UserTypeahead from '@/components/forms/UserTypeahead'

export default defineComponent({
  components: {
    UserTypeahead,
    CurrencyInput,
    Req,
    Link,
    HelpText,
    Select,
    DatePicker,
    InvoiceTypeahead,
    InputWrap,
    Fieldset,
    Button,
    CardAction,
    CardPadding,
    CardWrapper,
    Authenticated,
    Label,
  },
  props: {
    paymentMethods: Array,
    invoice: Object,
    paidBy: Object,
  },

  setup (props) {
    const form = useForm({
      invoice_uuid: props.invoice?.uuid,
      payment_method_id: null,
      paid_at: new Date,
      amount: null,
      made_by: null,
    })
    const save = () => {
      form.transform(data => ({
          ...data,
          invoice_uuid: selectedInvoice.value.uuid,
          maid_by: selectedUser.value?.id,
        }))
        .post('/payments')
    }
    const selectedInvoice = ref(props.invoice)
    const selectedUser = ref(props.paidBy)

    return {
      form,
      save,
      selectedInvoice,
      selectedUser,
    }
  }
})
</script>
