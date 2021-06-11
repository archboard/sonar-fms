<template>
  <form class="xl:col-span-3" @submit.prevent="reviewing = true">
    <Alert v-if="invoice.past_due" level="warning" class="mb-8">
      {{ __('This invoice is past due.') }}
    </Alert>
    <Alert v-if="form.hasErrors" level="error" class="mb-8">
      {{ __('Please correct the errors below and try again.') }}
    </Alert>

    <FormMultipartWrapper>
      <!-- Invoice details -->
      <div>
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

          <InputWrap>
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
              <CheckboxText>{{ __('Send notification') }}</CheckboxText>
            </CheckboxWrapper>
            <HelpText>
              {{ __("Having this option enabled will automatically queue an email to be sent notifying the appropriate parties of the available invoice. There is a 15-minute delay of sending the notification which allows you to make adjustments, cancel the notification, or delete the invoice all together. If this is not enabled, you may send a notification manually later.") }}
            </HelpText>
          </InputWrap>
        </Fieldset>
      </div>

      <!-- Invoice line items -->
      <div class="pt-8">
        <div class="mb-6">
          <CardSectionHeader>
            {{ __('Invoice line items') }}
          </CardSectionHeader>
          <HelpText class="text-sm mt-1">
            {{ __('Add line items to the build the invoice and total receivable amount.') }}
          </HelpText>
        </div>

        <Error v-if="form.errors.items">
          {{ __('You must have at least one invoice item.') }}
        </Error>

        <ul class="space-y-3">
          <TransitionGroup
            enter-active-class="transition duration-150 ease-in-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in-out"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
          >
            <li
              v-for="(item, index) in form.items"
              :key="item.id"
            >
              <CardWrapper>
                <CardPadding>
                  <Fieldset>
                    <InputWrap :error="form.errors[`items.${index}.fee_id`]">
                      <Label :for="`fee_id_${index}`">{{ __('Fee') }}</Label>
                      <Select
                        v-model="item.fee_id" :id="`fee_id_${index}`"
                        @change="feeSelected(item)"
                      >
                        <option :value="null">{{ __('Use a custom fee') }}</option>
                        <option
                          v-for="fee in fees"
                          :key="fee.id"
                          :value="fee.id"
                        >
                          {{ fee.name }}{{ fee.code ? ` (${fee.code})` : '' }} - {{ fee.amount_formatted }}
                        </option>
                      </Select>
                      <HelpText>
                        {{ __("Associating line items with a fee will help with reporting and syncing data, but isn't required.") }}
                      </HelpText>
                    </InputWrap>

                    <InputWrap :error="form.errors[`items.${index}.name`]">
                      <Label :for="`name_${index}`" :required="true">{{ __('Name') }}</Label>
                      <Input v-model="item.name" :id="`name_${index}`" />
                      <HelpText>
                        {{ __('This is the label given to the line item and will be displayed on the invoice.') }}
                      </HelpText>
                    </InputWrap>

                    <InputWrap :error="form.errors[`items.${index}.amount_per_unit`]">
                      <Label :for="`amount_per_unit_${index}`" :required="true">{{ __('Amount per unit') }}</Label>
                      <CurrencyInput v-model="item.amount_per_unit" :id="`amount_per_unit_${index}`" />
                    </InputWrap>

                    <InputWrap :error="form.errors[`items.${index}.quantity`]">
                      <Label :for="`quantity_${index}`" :required="true">{{ __('Quantity') }}</Label>
                      <Input v-model="item.quantity" :id="`quantity_${index}`" type="number" />
                    </InputWrap>

                    <div class="flex justify-between items-center">
                      <h4 class="font-bold">
                        {{ __('Line item total: :total', { total: displayCurrency(item.amount_per_unit * item.quantity) }) }}
                      </h4>
                      <Button color="red" size="sm" type="button" @click.prevent="form.items.splice(index, 1)">
                        <TrashIcon class="w-4 h-4" />
                        <span class="ml-2">{{ __('Remove line item') }}</span>
                      </Button>
                    </div>
                  </Fieldset>
                </CardPadding>
              </CardWrapper>
            </li>
          </TransitionGroup>
        </ul>

        <FadeIn>
          <CardWrapper v-if="form.items.length > 0" class="my-4">
            <CardPadding>
              <div class="flex justify-between">
                <h4 class="font-bold">
                  {{ __('Invoice subtotal') }}
                </h4>
                <div class="font-bold">
                  {{ displayCurrency(subtotal) }}
                </div>
              </div>
            </CardPadding>
          </CardWrapper>
        </FadeIn>

        <div class="relative flex justify-center mt-6">
          <button @click.prevent="addInvoiceLineItem" type="button" class="inline-flex items-center shadow-sm px-4 py-1.5 border border-gray-300 dark:border-gray-600 text-sm leading-5 font-medium rounded-full text-gray-700 dark:text-gray-100 bg-white dark:bg-gray-500 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
            <PlusSmIcon class="-ml-1.5 mr-1 h-5 w-5 text-gray-400 dark:text-gray-200" aria-hidden="true" />
            <span>{{ __('Add invoice line item') }}</span>
          </button>
        </div>
      </div>

      <!-- Scholarships -->
      <div class="pt-8">
        <div class="mb-6">
          <CardSectionHeader>
            {{ __('Scholarships') }}
          </CardSectionHeader>
          <HelpText class="text-sm mt-1">
            {{ __('Add scholarships to reduce the amount due for the invoice.') }}
          </HelpText>
        </div>

        <ul class="space-y-3">
          <TransitionGroup
            enter-active-class="transition duration-150 ease-in-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in-out"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
          >
            <li
              v-for="(item, index) in form.scholarships"
              :key="item.id"
            >
              <CardWrapper>
                <CardPadding>
                  <Fieldset>
                    <InputWrap :error="form.errors[`scholarships.${index}.scholarship_id`]">
                      <Label :for="`scholarship_id_${index}`">{{ __('Scholarship') }}</Label>
                      <Select
                        v-model="item.scholarship_id" :id="`scholarship_id_${index}`"
                        @change="scholarshipSelected(item)"
                      >
                        <option :value="null">{{ __('Use a custom scholarship') }}</option>
                        <option
                          v-for="scholarship in scholarships"
                          :key="scholarship.id"
                          :value="scholarship.id"
                        >
                          {{ scholarship.name }} - {{ scholarship.description }}
                        </option>
                      </Select>
                      <HelpText>
                        {{ __("Associating a scholarship will help with reporting and syncing data, but isn't required.") }}
                      </HelpText>
                    </InputWrap>

                    <InputWrap v-if="item.scholarship_id" :error="form.errors[`scholarships.${index}.sync_with_scholarship`]">
                      <CheckboxWrapper>
                        <Checkbox v-model:checked="item.sync_with_scholarship" @change="scholarshipSyncChanged(item)" />
                        <CheckboxText>{{ __('Sync details with associated scholarship.') }}</CheckboxText>
                      </CheckboxWrapper>
                      <HelpText>
                        {{ __("This option will keep the scholarship name, amount, percentage and resolution strategy in sync with the associated scholarship. This means that if you change the scholarship's name or amount, this line item will reflect those changes. If it is not enabled, the details set below will be set unless changed manually later.") }}
                      </HelpText>
                    </InputWrap>

                    <InputWrap v-if="!item.sync_with_scholarship" :error="form.errors[`scholarships.${index}.name`]">
                      <Label :for="`scholarship_name_${index}`" :required="true">{{ __('Name') }}</Label>
                      <Input v-model="item.name" :id="`scholarship_name_${index}`" />
                      <HelpText>
                        {{ __('This is the label given to the line item and will be displayed on the invoice.') }}
                      </HelpText>
                    </InputWrap>

                    <InputWrap v-if="!item.sync_with_scholarship" :error="form.errors[`scholarships.${index}.amount`]">
                      <Label :for="`scholarship_amount_${index}`">{{ __('Amount') }}</Label>
                      <CurrencyInput v-model="item.amount" :id="`scholarship_amount_${index}`" />
                    </InputWrap>

                    <InputWrap v-if="!item.sync_with_scholarship" :error="form.errors[`scholarships.${index}.percentage`]">
                      <Label :for="`scholarship_percentage_${index}`">{{ __('Percentage') }}</Label>
                      <Input v-model="item.percentage" :id="`scholarship_percentage_${index}`" />
                      <HelpText>
                        {{ __('This is the default scholarship percentage that will be applied to the invoice. This value is the percentage of the total invoice amount that has been deducted from the invoice. [invoice total] - ([invoice total] * [scholarship percentage]) = [total with scholarship applied].') }}
                      </HelpText>
                    </InputWrap>

                    <InputWrap v-if="!item.sync_with_scholarship && item.percentage && item.amount" :error="form.errors[`scholarships.${index}.resolution_strategy`]">
                      <Label for="resolution_strategy">{{ __('Resolution strategy') }}</Label>
                      <Select v-model="item.resolution_strategy" id="resolution_strategy">
                        <option
                          v-for="(label, strategy) in strategies"
                          :key="strategy"
                          :value="strategy"
                        >
                          {{ label }}
                        </option>
                      </Select>
                      <HelpText>
                        {{ __('This resolves whether to use the percentage or amount for the scholarship when both are provided. Least will use whichever has the least amount of discount. Greatest will use whichever has the greatest discount.') }}
                      </HelpText>
                    </InputWrap>

                    <InputWrap v-if="form.items.length > 1">
                      <HelpText>
                        {{ __('Choose the items for which this scholarship applies. If no items are selected, it will be applied to the entire invoice total.') }}
                      </HelpText>
                      <div class="mt-3 space-y-1">
                        <div
                          v-for="lineItem in form.items"
                          :key="lineItem.id"
                        >
                          <CheckboxWrapper>
                            <Checkbox v-model:checked="item.applies_to" :value="lineItem.id" />
                            <CheckboxText>{{ lineItem.name }}</CheckboxText>
                          </CheckboxWrapper>
                        </div>
                      </div>
                    </InputWrap>

                    <div class="flex justify-between items-center">
                      <h4 class="font-bold">
                        {{ __('Discount total: :total', { total: displayCurrency(getItemDiscount(item)) }) }}
                      </h4>
                      <Button color="red" size="sm" type="button" @click.prevent="form.scholarships.splice(index, 1)">
                        <TrashIcon class="w-4 h-4" />
                        <span class="ml-2">{{ __('Remove scholarship') }}</span>
                      </Button>
                    </div>
                  </Fieldset>
                </CardPadding>
              </CardWrapper>
            </li>
          </TransitionGroup>
        </ul>

        <div class="relative flex justify-center mt-6">
          <button @click.prevent="addScholarship" type="button" class="inline-flex items-center shadow-sm px-4 py-1.5 border border-gray-300 dark:border-gray-600 text-sm leading-5 font-medium rounded-full text-gray-700 dark:text-gray-100 bg-white dark:bg-gray-500 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
            <PlusSmIcon class="-ml-1.5 mr-1 h-5 w-5 text-gray-400 dark:text-gray-200" aria-hidden="true" />
            <span>{{ __('Add scholarship') }}</span>
          </button>
        </div>
      </div>

      <!-- Payment schedules -->
      <div class="pt-8">
        <div class="mb-6">
          <CardSectionHeader>
            {{ __('Payment schedules') }}
          </CardSectionHeader>
          <HelpText class="text-sm mt-1">
            {{ __('Add available payment schedules to allow the invoice to be paid in separate payments rather than all at once.') }}
          </HelpText>
        </div>

        <ul class="space-y-3">
          <TransitionGroup
            enter-active-class="transition duration-150 ease-in-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in-out"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
          >
            <li
              v-for="(item, index) in form.payment_schedules"
              :key="item.id"
              class="bg-gray-100 dark:bg-gray-800 shadow overflow-hidden rounded-md p-6"
            >
              <ul class="flex flex-wrap -mx-2">
                <TransitionGroup
                  enter-active-class="transition duration-150 ease-in-out"
                  enter-from-class="opacity-0"
                  enter-to-class="opacity-100"
                  leave-active-class="transition duration-150 ease-in-out"
                  leave-from-class="opacity-100"
                  leave-to-class="opacity-0"
                >
                  <li
                    v-for="(term, termIndex) in item.terms"
                    :key="term.id"
                    class="px-2 w-full sm:w-1/2 md:w-full lg:w-1/2 xl:w-1/3 mb-4"
                  >
                    <div class="rounded-md border border-gray-200 bg-gray-200 dark:bg-gray-800 dark:border-gray-500 p-3">
                      <Fieldset>
                        <InputWrap :error="form.errors[`payment_schedules.${index}.terms.${termIndex}.amount`]">
                          <Label :required="true" :for="`schedule_${index}_${termIndex}_amount`">{{ __('Amount') }}</Label>
                          <CurrencyInput v-model="term.amount" :id="`schedule_${index}_${termIndex}_amount`" />
                        </InputWrap>

                        <InputWrap :error="form.errors[`payment_schedules.${index}.terms.${termIndex}.due_at`]">
                          <Label :for="`schedule_${index}_${termIndex}_due_at`">{{ __('Due date') }}</Label>
                          <DatePicker :id="`schedule_${index}_${termIndex}_due_at`" v-model="term.due_at" />
                        </InputWrap>

                        <div class="flex justify-end">
                          <Button color="red" @click.prevent="removePaymentTerm(item, termIndex)" size="xs">
                            <TrashIcon class="w-4 h-4" />
                            <span class="ml-2">{{ __('Remove term') }}</span>
                          </Button>
                        </div>
                      </Fieldset>
                    </div>
                  </li>
                </TransitionGroup>

                <!-- Mock term that just has the button -->
                <li class="px-2 w-full md:w-1/2 lg:w-1/3 relative">
                  <div class="opacity-50 rounded-md border border-gray-200 bg-gray-200 dark:bg-gray-800 dark:border-gray-500 p-3">
                    <Fieldset>
                      <InputWrap>
                        <Mocker :inline="true">
                          <Label>&nbsp;</Label>
                        </Mocker>
                        <Mocker>
                          <CurrencyInput />
                        </Mocker>
                      </InputWrap>

                      <InputWrap>
                        <Mocker :inline="true">
                          <Label>&nbsp;</Label>
                        </Mocker>
                        <Mocker>
                          <Input />
                        </Mocker>
                      </InputWrap>

                      <div class="flex justify-end">
                        <Mocker :inline="true">
                          <Button color="red" size="xs">
                            <TrashIcon class="w-4 h-4" />
                            <span class="ml-2">{{ __('Remove term') }}</span>
                          </Button>
                        </Mocker>
                      </div>
                    </Fieldset>
                  </div>

                  <div class="absolute inset-0 -mt-4 flex items-center justify-center">
                    <Button @click.prevent="addPaymentTerm(item)" size="sm">
                      {{ __('Add payment term') }}
                    </Button>
                  </div>
                </li>
              </ul>

              <div class="flex justify-between items-center pt-6">
                <h4 class="font-bold">
                  {{ __('Total with schedule: :total', { total: displayCurrency(getScheduleTotal(item)) }) }}
                </h4>
                <Button color="red" size="sm" type="button" @click.prevent="form.payment_schedules.splice(index, 1)">
                  <TrashIcon class="w-4 h-4" />
                  <span class="ml-2">{{ __('Remove schedule') }}</span>
                </Button>
              </div>
            </li>
          </TransitionGroup>
        </ul>

        <div class="relative flex justify-center mt-6">
          <button @click.prevent="addPaymentSchedule" type="button" class="inline-flex items-center shadow-sm px-4 py-1.5 border border-gray-300 dark:border-gray-600 text-sm leading-5 font-medium rounded-full text-gray-700 dark:text-gray-100 bg-white dark:bg-gray-500 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
            <PlusSmIcon class="-ml-1.5 mr-1 h-5 w-5 text-gray-400 dark:text-gray-200" aria-hidden="true" />
            <span>{{ __('Add payment schedule') }}</span>
          </button>
        </div>
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
    :auto-close="false"
  >
    <InvoiceSummary :invoice="form" />
  </Modal>
</template>

<script>
import { computed, inject, ref, watch, watchEffect } from 'vue'
import { useForm, usePage } from '@inertiajs/inertia-vue3'
import { PlusSmIcon } from '@heroicons/vue/solid'
import { TrashIcon } from '@heroicons/vue/outline'
import Fieldset from '../../components/forms/Fieldset'
import CardHeader from '../../components/CardHeader'
import HelpText from '../../components/HelpText'
import InputWrap from '../../components/forms/InputWrap'
import Checkbox from '../../components/forms/Checkbox'
import Label from '../../components/forms/Label'
import CheckboxText from '../../components/forms/CheckboxText'
import CheckboxWrapper from '../../components/forms/CheckboxWrapper'
import fetchesTerms from '../../composition/fetchesTerms'
import Select from '../../components/forms/Select'
import Textarea from '../../components/forms/Textarea'
import displaysCurrency from '../../composition/displaysCurrency'
import fetchesResolutionStrategies from '../../composition/fetchesResolutionStrategies'
import Input from '../../components/forms/Input'
import Button from '../../components/Button'
import FormMultipartWrapper from '../../components/forms/FormMultipartWrapper'
import CardSectionHeader from '../../components/CardSectionHeader'
import dayjs from '@/plugins/dayjs'
import FadeIn from '../../components/transitions/FadeIn'
import Error from '../../components/forms/Error'
import DatePicker from '../../components/forms/DatePicker'
import Alert from '../../components/Alert'
import displaysDate from '../../composition/displaysDate'
import invoiceItemForm from '../../composition/invoiceItemForm'
import invoiceScholarshipForm from '../../composition/invoiceScholarshipForm'
import invoicePaymentScheduleForm from '../../composition/invoicePaymentScheduleForm'
import CurrencyInput from '../../components/forms/CurrencyInput'
import CardWrapper from '../../components/CardWrapper'
import CardPadding from '../../components/CardPadding'
import InvoiceSummary from '../../components/InvoiceSummary'
import Mocker from '../../components/Mocker'
import Modal from '../../components/Modal'

export default {
  components: {
    Modal,
    Mocker,
    InvoiceSummary,
    CardPadding,
    CardWrapper,
    CurrencyInput,
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
    PlusSmIcon,
    TrashIcon,
    DatePicker,
  },
  props: {
    student: {
      type: Object,
      default: () => ({})
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
    const $route = inject('$route')
    const { terms } = fetchesTerms()
    const reviewing = ref(false)
    const { strategies } = fetchesResolutionStrategies()
    const isNew = computed(() => !props.invoice.id)
    const page = usePage()
    const isDark = computed(() => window.isDark)
    const form = useForm({
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
    })
    // Emit the initial value
    emit('update:invoiceForm', form)

    const school = computed(() => page.props.value.school)

    const { timezone, displayDate, getDate } = displaysDate()
    const { displayCurrency } = displaysCurrency()
    const total = computed(() => {
      let total = subtotal.value - scholarshipSubtotal.value

      if (total < 0) {
        total = 0
      }

      return total
    })
    const totalDue = computed(() => displayCurrency(total.value))

    const saveInvoice = close => {
      const route = props.invoice.id
        ? $route('students.invoices.update', [props.student, props.invoice])
        : $route('students.invoices.store', [props.student])
      const method = props.invoice.id
        ? 'put'
        : 'post'

      form[method](route, {
        onSuccess () {
          if (typeof close === 'function') {
            close()
          }
        },
        onFinish () {
          form.processing = false
        }
      })
    }

    // Watch for changes to apply a template
    watch(() => props.invoiceTemplate, () => {
      console.log('watch', props.invoiceTemplate)
      Object.keys(form.data())
        .forEach(field => {
          if (typeof props.invoiceTemplate[field] !== 'undefined') {
            form[field] = props.invoiceTemplate[field]
          }
        })
    })

    watch(form, () => {
      emit('update:invoiceForm', form)
    })

    // Invoice line items
    const {
      fees,
      subtotal,
      addInvoiceLineItem,
      feeSelected
    } = invoiceItemForm(form)

    // Scholarships
    const {
      scholarships,
      scholarshipSubtotal,
      getItemDiscount,
      addScholarship,
      scholarshipSelected
    } = invoiceScholarshipForm(form)

    // Payment schedules
    const {
      addPaymentSchedule,
      addPaymentTerm,
      getScheduleTotal,
      removePaymentTerm,
    } = invoicePaymentScheduleForm(form, total)

    return {
      reviewing,
      isNew,
      school,
      terms,
      fees,
      form,
      subtotal,
      saveInvoice,
      addInvoiceLineItem,
      displayCurrency,
      feeSelected,
      isDark,
      displayDate,
      timezone,
      getItemDiscount,
      scholarshipSubtotal,
      total,
      totalDue,
      strategies,
      scholarships,
      addScholarship,
      scholarshipSelected,
      addPaymentSchedule,
      addPaymentTerm,
      getScheduleTotal,
      removePaymentTerm,
    }
  },
}
</script>
