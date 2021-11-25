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
          :allow-student-editing="allowStudentEditing"
        />
      </div>

      <!-- Invoice details -->
      <div class="pt-8">
        <InvoiceDetailsForm v-model="form" />
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
        <InvoiceTax v-model="form" />
      </div>
    </FormMultipartWrapper>
  </form>

  <div class="mt-8 p-4 border-t border-gray-400 bg-gray-200 dark:bg-gray-700 dark:border-gray-300 rounded-b-md space-x-3">
    <Button type="button" @click.prevent="reviewing = true">
      {{ __('Review and publish') }}
    </Button>
    <Button v-if="isNew" type="button" @click.prevent="saveAsDraft" color="white">
      {{ __('Save as draft') }}
    </Button>
    <Button v-else type="button" @click.prevent="updateDraft" color="white">
      {{ __('Update draft') }}
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
import { computed, inject, ref, watch, watchEffect } from 'vue'
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
import InvoiceTax from '@/components/forms/invoices/InvoiceTax'
import InvoiceDetails from '@/components/InvoiceDetails'
import InvoiceDetailsForm from '@/components/forms/invoices/InvoiceDetailsForm'
import isUndefined from 'lodash/isUndefined'

export default {
  components: {
    InvoiceDetailsForm,
    InvoiceDetails,
    InvoiceTax,
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
    allowStudentEditing: {
      type: Boolean,
      default: true,
    },
  },
  emits: ['update:invoiceForm'],

  setup (props, { emit }) {
    const $route = inject('$route')
    const students = useProp('students')
    const reviewing = ref(false)
    const { strategies } = fetchesResolutionStrategies()
    const isNew = computed(() => !props.invoice.uuid)
    const { school } = useSchool()
    const form = useForm({
      students: students.value || props.invoice.students || [],
      title: props.invoice.title || school.value.default_title,
      description: props.invoice.description || null,
      term_id: props.invoice.term_id || null,
      invoice_date: props.invoice.invoice_date
        ? dayjs(props.invoice.invoice_date).toDate()
        : dayjs().toDate(),
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
      apply_tax: isUndefined(props.invoice.apply_tax) ? true : props.invoice.apply_tax,
      use_school_tax_defaults: isUndefined(props.invoice.use_school_tax_defaults) ? true : props.invoice.use_school_tax_defaults,
      apply_tax_to_all_items: isUndefined(props.invoice.apply_tax_to_all_items) ? true : props.invoice.apply_tax_to_all_items,
      tax_items: props.invoice.tax_items || [],
      tax_rate: isUndefined(props.invoice.tax_rate) ? (school.value.tax_rate_converted || null) : props.invoice.tax_rate,
      tax_label: isUndefined(props.invoice.tax_label) ? (school.value.tax_label || null) : props.invoice.tax_label,
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

    const options = {
      onFinish () {
        form.processing = false
      }
    }
    const saveInvoice = () => {
      form[props.method](props.endpoint, options)
    }
    const saveAsDraft = () => {
      form.post($route('invoices.store.draft'), options)
    }
    const updateDraft = () => {
      if (props.allowStudentEditing) {
        form.post($route('batches.draft', props.invoice.batch_id), options)
      } else {
        form.put($route('invoices.update.draft', props.invoice.uuid), options)
      }
    }

    // Watch for changes to apply a template
    applyTemplate(props.invoiceTemplate)
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
      form,
      saveInvoice,
      displayCurrency,
      total,
      totalDue,
      strategies,
      saveAsDraft,
      updateDraft,
    }
  },
}
</script>
