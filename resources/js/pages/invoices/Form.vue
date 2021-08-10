<template>
  <form class="xl:col-span-3" @submit.prevent="reviewing = true">
    <Alert v-if="invoice.past_due" level="warning" class="mb-8">
      {{ __('This invoice is past due.') }}
    </Alert>
    <Alert v-if="form.hasErrors" level="error" class="mb-8">
      {{ __('Please correct the errors below and try again.') }}
    </Alert>

    <FormMultipartWrapper>
      <!-- Students -->
      <div>
        <InvoiceStudents
          v-model="form.students"
          :form="form"
        />
      </div>

      <!-- Invoice details -->
      <div class="pt-8">
        <div class="mb-6">
          <CardSectionHeader>{{ __('Invoice details') }}</CardSectionHeader>
          <HelpText>
            {{ __('These are the general details about the invoice.') }}
          </HelpText>
        </div>
        <Fieldset>
          <InputWrap :error="form.errors.title">
            <Label for="title" :required="true">{{ __('Title') }}</Label>
            <Input v-model="form.title" id="title" required autofocus />
            <HelpText>
              {{ __('Give the invoice a meaningful title that is easily recognizable and descriptive.') }}
            </HelpText>
          </InputWrap>

          <InputWrap>
            <Label for="description">{{ __('Description') }}</Label>
            <Textarea v-model="form.description" id="description" />
            <HelpText>
              {{ __('This is a description of the invoice that will be displayed with the invoice.') }}
            </HelpText>
          </InputWrap>

          <InputWrap :error="form.errors.available_at">
            <Label for="available_at">{{ __('Availability') }}</Label>
            <DatePicker v-model="form.available_at" id="available_at" />
            <HelpText>
              {{ __("Set a date and time that this invoice is available to the student's guardians or other contacts. Before the configured time, it will only be viewable to admins. This is helpful to use if you want to prepare and preview invoices before actually making them available for the student. The time is based on your current timezone of :timezone. If this timezone is incorrect you can change it in your Personal Settings.", { timezone }) }}
            </HelpText>
          </InputWrap>

          <InputWrap :error="form.errors.due_at">
            <Label for="due_at">{{ __('Due date') }}</Label>
            <DatePicker v-model="form.due_at" id="due_at" />
            <HelpText>
              {{ __("Set the date and time that this invoice is due, or don't set one to not have a due date. The time is based on your current timezone of :timezone. If this timezone is incorrect you can change it in your Personal Settings.", { timezone }) }}
            </HelpText>
          </InputWrap>

          <InputWrap :error="form.errors.term_id">
            <Label for="term_id">{{ __('Term') }}</Label>
            <Select v-model="form.term_id" id="term_id">
              <option :value="null">{{ __('No term') }}</option>
              <option
                v-for="term in terms"
                :key="term.id"
                :value="term.id"
              >
                {{ term.school_years }} - {{ term.name }}
              </option>
            </Select>
            <HelpText>{{ __('Associating a term with an invoice allows you to group invoices by school term and offers another reporting perspective.') }}</HelpText>
          </InputWrap>

          <InputWrap>
            <CheckboxWrapper>
              <Checkbox v-model:checked="form.notify" />
              <CheckboxText>{{ __('Queue notification') }}</CheckboxText>
            </CheckboxWrapper>
            <HelpText>
              {{ __("Having this option enabled will automatically queue an email to be sent notifying the appropriate parties of the available invoice. There is a 15-minute delay of sending the notification which allows you to make adjustments, cancel the notification, or delete the invoice all together. If this is not enabled, you may send a notification manually later.") }}
            </HelpText>
          </InputWrap>
        </Fieldset>
      </div>

      <!-- Invoice line items -->
      <div class="pt-8">
        <InvoiceItems
          v-model="form.items"
          :form="form"
        />
      </div>

      <!-- Scholarships -->
      <div class="pt-8">
        <InvoiceScholarships
          v-model="form.scholarships"
          :form="form"
        />
      </div>

      <!-- Payment schedules -->
      <div class="pt-8">
        <InvoicePaymentSchedules
          v-model="form.payment_schedules"
          :form="form"
          :total="total"
        />
      </div>

      <!-- Tax -->
      <div class="pt-8" v-if="school.collect_tax">
        <div class="mb-6">
          <CardSectionHeader>
            {{ __('Taxes') }}
          </CardSectionHeader>
          <HelpText class="text-sm mt-1">
            {{ __('Add tax details for this invoice.') }}
          </HelpText>
        </div>

        <Fieldset>
          <InputWrap :error="form.errors.apply_tax">
            <CheckboxWrapper>
              <Checkbox v-model:checked="form.apply_tax" />
              <CheckboxText>{{ __('Apply tax rate to this invoice.') }}</CheckboxText>
            </CheckboxWrapper>
            <HelpText>{{ __('When this option is enabled, a tax is added to the amount due.') }}</HelpText>
          </InputWrap>

          <FadeIn>
            <InputWrap v-if="form.apply_tax" :error="form.errors.use_school_tax_defaults">
              <CheckboxWrapper>
                <Checkbox v-model:checked="form.use_school_tax_defaults" />
                <CheckboxText>{{ __('Use school default tax rate and label - :label (:rate).', { label: school.tax_label, rate: school.tax_rate_formatted }) }}</CheckboxText>
              </CheckboxWrapper>
            </InputWrap>
          </FadeIn>

          <FadeInGroup>
            <InputWrap v-if="form.apply_tax && !form.use_school_tax_defaults" :error="form.errors.tax_rate">
              <Label for="tax_rate" :required="true">{{ __('Tax rate') }}</Label>
              <Input v-model="form.tax_rate" id="tax_rate" />
              <HelpText>{{ __('This is the tax rate percentage to be applied to this invoice.') }}</HelpText>
            </InputWrap>

            <InputWrap v-if="form.apply_tax && !form.use_school_tax_defaults" :error="form.errors.tax_label">
              <Label for="tax_label" :required="true">{{ __('Tax label') }}</Label>
              <Input v-model="form.tax_label" id="tax_label" placeholder="VAT" />
              <HelpText>{{ __('This is the label that will be displayed for the name/type of tax.') }}</HelpText>
            </InputWrap>
          </FadeInGroup>
        </Fieldset>
      </div>
    </FormMultipartWrapper>
  </form>

  <div class="mt-8 p-4 border-t border-gray-400 bg-gray-200 dark:bg-gray-700 dark:border-gray-300 rounded-b-md">
    <Button type="button" size="lg" @click.prevent="reviewing = true">
      {{ __('Review and save') }}
    </Button>
  </div>

  <Modal
    v-if="reviewing"
    @close="reviewing = false"
    @action="saveInvoice"
    :action-loading="form.processing"
    size="xl"
  >
    <InvoiceSummary :invoice="form" />
  </Modal>
</template>

<script>
import { computed, ref, watch, watchEffect } from 'vue'
import { useForm } from '@inertiajs/inertia-vue3'
import Fieldset from '@/components/forms/Fieldset'
import CardHeader from '@/components/CardHeader'
import HelpText from '@/components/HelpText'
import InputWrap from '@/components/forms/InputWrap'
import Checkbox from '@/components/forms/Checkbox'
import Label from '@/components/forms/Label'
import CheckboxText from '@/components/forms/CheckboxText'
import CheckboxWrapper from '@/components/forms/CheckboxWrapper'
import fetchesTerms from '@/composition/fetchesTerms'
import Select from '@/components/forms/Select'
import Textarea from '@/components/forms/Textarea'
import displaysCurrency from '@/composition/displaysCurrency'
import fetchesResolutionStrategies from '@/composition/fetchesResolutionStrategies'
import Input from '@/components/forms/Input'
import Button from '@/components/Button'
import FormMultipartWrapper from '@/components/forms/FormMultipartWrapper'
import CardSectionHeader from '@/components/CardSectionHeader'
import dayjs from '@/plugins/dayjs'
import FadeIn from '@/components/transitions/FadeIn'
import Error from '@/components/forms/Error'
import DatePicker from '@/components/forms/DatePicker'
import Alert from '@/components/Alert'
import displaysDate from '@/composition/displaysDate'
import invoiceItemForm from '@/composition/invoiceItemForm'
import invoiceScholarshipForm from '@/composition/invoiceScholarshipForm'
import CardWrapper from '@/components/CardWrapper'
import CardPadding from '@/components/CardPadding'
import InvoiceSummary from '@/components/InvoiceSummary'
import Modal from '@/components/Modal'
import FadeInGroup from '@/components/transitions/FadeInGroup'
import useSchool from '@/composition/useSchool'
import InvoiceItems from '@/components/forms/invoices/InvoiceItems'
import InvoiceScholarships from '@/components/forms/invoices/InvoiceScholarships'
import InvoicePaymentSchedules from '@/components/forms/invoices/InvoicePaymentSchedules'
import InvoiceStudents from '@/components/forms/invoices/InvoiceStudents'
import useProp from '@/composition/useProp'

export default {
  components: {
    InvoiceStudents,
    InvoicePaymentSchedules,
    InvoiceScholarships,
    InvoiceItems,
    FadeInGroup,
    Modal,
    InvoiceSummary,
    CardPadding,
    CardWrapper,
    Alert,
    Error,
    FadeIn,
    CardSectionHeader,
    FormMultipartWrapper,
    Button,
    Input,
    Textarea,
    Select,
    CheckboxWrapper,
    CheckboxText,
    Checkbox,
    InputWrap,
    HelpText,
    CardHeader,
    Fieldset,
    Label,
    DatePicker,
  },
  props: {
    endpoint: {
      type: String,
      required: true,
    },
    method: {
      type: String,
      required: true,
    },
    invoice: {
      type: Object,
      default: () => ({})
    },
    invoiceTemplate: {
      type: Object,
      default: () => ({})
    },
    invoiceForm: {
      type: Object,
      default: () => ({})
    },
  },
  emits: ['update:invoiceForm'],

  setup (props, { emit }) {
    const students = useProp('students')
    const { terms } = fetchesTerms()
    const reviewing = ref(false)
    const { strategies } = fetchesResolutionStrategies()
    const isNew = computed(() => !props.invoice.id)
    const { school } = useSchool()
    const form = useForm({
      students: students.value,
      title: props.invoice.title || null,
      description: props.invoice.description || null,
      term_id: props.invoice.term_id || null,
      available_at: props.invoice.available_at
        ? dayjs(props.invoice.available_at).toDate()
        : null,
      due_at: props.invoice.due_at
        ? dayjs(props.invoice.due_at).toDate()
        : null,
      notify: props.invoice.notify || false,
      items: props.invoice.items || [],
      scholarships: props.invoice.scholarships || [],
      payment_schedules: props.invoice.payment_schedules || [],
      apply_tax: true,
      use_school_tax_defaults: true,
      tax_rate: school.value.tax_rate_converted || null,
      tax_label: school.value.tax_label || null,
    })
    const applyTemplate = template => {
      Object.keys(form.data())
        .forEach(field => {
          if (typeof template[field] !== 'undefined') {
            form[field] = template[field]
          }
        })
    }

    // Emit the initial value
    emit('update:invoiceForm', form)

    const { timezone, displayDate } = displaysDate()
    const { displayCurrency } = displaysCurrency()

    // Numbers
    const { subtotal } = invoiceItemForm(form)
    const { scholarshipSubtotal } = invoiceScholarshipForm(form)
    const total = computed(() => {
      let total = subtotal.value - scholarshipSubtotal.value

      if (total < 0) {
        total = 0
      }

      return total
    })
    const totalDue = computed(() => displayCurrency(total.value))

    const saveInvoice = () => {
      form[props.method](props.endpoint, {
        onFinish () {
          form.processing = false
        }
      })
    }

    // Watch for changes to apply a template
    watch(() => props.invoiceTemplate, state => {
      applyTemplate(state)
    })

    watch(() => form, state => {
      emit('update:invoiceForm', state)
    }, { deep: true })

    return {
      reviewing,
      isNew,
      school,
      terms,
      form,
      saveInvoice,
      displayCurrency,
      displayDate,
      timezone,
      total,
      totalDue,
      strategies,
    }
  },
}
</script>
