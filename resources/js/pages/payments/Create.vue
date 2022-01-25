<template>
  <Authenticated>
    <form @submit.prevent="save">
      <CardWrapper>
        <CardPadding>
          <Fieldset>
            <div>
              <FadeIn>
                <Alert v-if="selectedInvoice.parent" level="warning" class="mb-4">
                  {{ __('This invoice is part of the combined invoice :invoice. This payment will also be recorded for that invoice.', { invoice: `${selectedInvoice.parent.title} (${selectedInvoice.parent.invoice_number})` }) }}
                  <InertiaLink class="font-medium text-yellow-50 hover:text-white hover:underline" :href="`/payments/create?invoice_uuid=${selectedInvoice.parent_uuid}`">{{ __('Record a payment for that invoice') }}</InertiaLink>.
                </Alert>
              </FadeIn>
              <FadeIn>
                <div v-if="selectedInvoice.children && selectedInvoice.children.length > 0" class="mb-4">
                  <Alert class="mb-4">
                    {{ __('This invoice is a combined invoice. The payment will be distributed evenly to the following invoices. If you want to apply a payment to a specific invoice, you may do so below.') }}
                  </Alert>

                  <ChildInvoices :invoices="selectedInvoice.children" />
                </div>
              </FadeIn>

              <InputWrap :error="form.errors.invoice_uuid">
                <Label for="invoice_uuid">{{ __('Invoice') }} <Req /></Label>
                <InvoiceTypeahead v-model="selectedInvoice" id="invoice_uuid" />
                <HelpText>{{ __('This is the invoice for which you are recording a payment.') }}</HelpText>
              </InputWrap>
            </div>

            <FadeIn>
              <InputWrap v-if="selectedInvoice.payment_schedules && selectedInvoice.payment_schedules.length > 0" :error="form.errors.invoice_payment_term_uuid">
                <Label for="invoice_payment_term_uuid">{{ __('Payment term') }}</Label>
                <Select v-model="form.invoice_payment_term_uuid" id="invoice_payment_term_uuid">
                  <option :value="null">{{ __('N/A') }}</option>
                  <optgroup
                    v-for="schedule in selectedInvoice.payment_schedules"
                    :key="schedule.id"
                    :label="__(':count payments', { count: schedule.terms.length })"
                  >
                    <option
                      v-for="term in schedule.terms"
                      :key="term.uuid"
                      :value="term.uuid"
                      :disabled="term.remaining_balance <= 0"
                    >
                      <template v-if="term.due_at">
                        {{ __(':amount due on :date (:remaining remaining)', { amount: displayCurrency(term.amount), date: displayDate(term.due_at, 'abbr_date'), remaining: displayCurrency(term.remaining_balance) }) }}
                      </template>
                      <template v-else>
                        {{ __(':amount due (:remaining remaining)', { amount: displayCurrency(term.amount), remaining: displayCurrency(term.remaining_balance) }) }}
                      </template>
                    </option>
                  </optgroup>
                </Select>
                <HelpText>{{ __("Setting a payment term will use the term's schedule's total for invoice's total when calculating the remaining balance.") }}</HelpText>
              </InputWrap>
            </FadeIn>

            <InputWrap :error="form.errors.payment_method_id">
              <Label for="payment_method_id">{{ __('Payment method') }}</Label>
              <PaymentMethodSelector v-model="form.payment_method_id" id="payment_method_id" />
              <HelpText>{{ __("Associating a payment method isn't required, but could be helpful for record keeping.") }} <Link href="/payment-methods">{{ __("Manage payment methods") }}.</Link></HelpText>
            </InputWrap>

            <InputWrap :error="form.errors.transaction_details">
              <Label for="transaction_details">{{ __('Transaction details') }}</Label>
              <Input v-model="form.transaction_details" id="transaction_details" />
              <HelpText>{{ __("This could hold additional information about the payment, such as a transaction number, to add more auditable details about the payment.") }}</HelpText>
            </InputWrap>

            <InputWrap :error="form.errors.paid_at">
              <Label for="paid_at">{{ __('Date paid') }} <Req /></Label>
              <DatePicker v-model="form.paid_at" mode="date" id="paid_at" />
            </InputWrap>

            <InputWrap :error="form.errors.amount">
              <Label for="amount">{{ __('Amount') }} <Req /></Label>
              <CurrencyInput v-model="form.amount" id="amount" />
              <HelpText v-if="selectedTerm.uuid">{{ __('The remaining balance for the selected term is :amount.', { amount: displayCurrency(selectedTerm.remaining_balance) }) }}</HelpText>
              <HelpText v-else-if="selectedInvoice.remaining_balance_formatted">{{ __('The remaining balance is :amount.', { amount: selectedInvoice.remaining_balance_formatted }) }}</HelpText>
            </InputWrap>

            <InputWrap :error="form.errors.made_by">
              <Label for="made_by">{{ __('Paid by') }}</Label>
              <UserTypeahead v-model="selectedUser" id="made_by" />
              <HelpText>{{ __('Associating a payment to a user is helpful for historical records.') }}</HelpText>
            </InputWrap>

            <InputWrap :error="form.errors.notes">
              <Label for="notes">{{ __('Notes') }}</Label>
              <Textarea v-model="form.notes" id="notes" />
              <HelpText>{{ __('Optional additional internal notes. Only other administrators can view these notes.') }}</HelpText>
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
import { computed, defineComponent, ref } from 'vue'
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
import FadeIn from '@/components/transitions/FadeIn'
import displaysDate from '@/composition/displaysDate'
import displaysCurrency from '@/composition/displaysCurrency'
import isEmpty from 'lodash/isEmpty'
import Alert from '@/components/Alert'
import ChildInvoices from '@/components/ChildInvoices'
import Input from '@/components/forms/Input'
import Textarea from '@/components/forms/Textarea'
import PaymentMethodSelector from '@/components/forms/PaymentMethodSelector'

export default defineComponent({
  components: {
    PaymentMethodSelector,
    Textarea,
    Input,
    ChildInvoices,
    Alert,
    FadeIn,
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
    invoice: Object,
    paidBy: Object,
    term: String,
  },

  setup (props) {
    const form = useForm({
      invoice_uuid: props.invoice?.uuid,
      invoice_payment_term_uuid: props.term || null,
      payment_method_id: null,
      transaction_details: null,
      paid_at: new Date,
      amount: null,
      made_by: null,
      notes: null,
    })
    const save = () => {
      form.transform(data => ({
          ...data,
          invoice_uuid: selectedInvoice.value.uuid,
          made_by: selectedUser.value?.id,
        }))
        .post('/payments')
    }
    const selectedInvoice = ref(props.invoice)
    const selectedUser = ref(props.paidBy)
    const selectedTerm = computed(() => {
      if (!form.invoice_payment_term_uuid || !selectedInvoice.value.payment_schedules) {
        return {}
      }

      return selectedInvoice.value.payment_schedules.reduce((obj, sch) => {
        if (!isEmpty(obj)) {
          return obj
        }

        const term = sch.terms.find(term => term.uuid === form.invoice_payment_term_uuid)

        if (term) {
          obj = term
        }

        return obj
      }, {})
    })
    const { displayDate } = displaysDate()
    const { displayCurrency } = displaysCurrency()

    return {
      form,
      save,
      selectedInvoice,
      selectedUser,
      displayDate,
      displayCurrency,
      selectedTerm,
    }
  }
})
</script>
