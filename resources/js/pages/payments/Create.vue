<template>
  <Authenticated>
    <form @submit.prevent="save">
      <CardWrapper>
        <CardPadding>
          <Fieldset>
            <InputWrap :error="form.errors.invoice_uuid">
              <Label for="invoice_uuid">{{ __('Invoice') }}</Label>
              <InvoiceTypeahead v-model="selectedInvoice" id="invoice_uuid" />
            </InputWrap>
          </Fieldset>
        </CardPadding>
        <CardAction>
          <Button type="submit" :loading="form.processing">
            {{ __('Save') }}
          </Button>
        </CardAction>
      </CardWrapper>
    </form>
    <pre>{{ selectedInvoice }}</pre>
    <pre>{{ form }}</pre>
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
import InvoiceTypeahead from '@/components/forms/InvoiceTypeahead'

export default defineComponent({
  components: {
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
        }))
        .post('/payments')
    }
    const selectedInvoice = ref(props.invoice)

    return {
      form,
      save,
      selectedInvoice,
    }
  }
})
</script>
