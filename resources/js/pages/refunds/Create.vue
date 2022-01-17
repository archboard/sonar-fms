<template>
  <Authenticated>
    <form @submit.prevent="save">
      <CardWrapper>
        <CardPadding>
          <Fieldset>
            <InputWrap :error="form.errors.amount">
              <Label for="amount" required>{{ __('Refund amount') }}</Label>
              <CurrencyInput v-model="form.amount" id="amount" />
            </InputWrap>

            <InputWrap :error="form.errors.transactions_details">
              <Label for="transactions_details">{{ __('Transaction details') }}</Label>
              <Input v-model="form.transactions_details" id="transactions_details" />
            </InputWrap>

            <InputWrap :error="form.errors.notes">
              <Label for="notes">{{ __('Notes') }}</Label>
              <Textarea v-model="form.notes" id="notes" />
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

export default defineComponent({
  components: {
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
      transactions_details: null,
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
