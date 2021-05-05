<template>
  <Slideout
    @close="$emit('close')"
    @action="saveInvoice"
    :auto-close="false"
    :processing="saving"
  >
    <template v-slot:header>
      <div class="space-y-1">
        <CardHeader>
          {{ __('New invoice for :name', { name: student.full_name }) }}
        </CardHeader>
      </div>
    </template>

    <FormMultipartWrapper>
      <div>
        <div class="mb-6">
          <CardSectionHeader>{{ __('Invoice details') }}</CardSectionHeader>
          <HelpText>
            {{ __('These are the general details about the invoice.') }}
          </HelpText>
        </div>
        <Fieldset>
          <InputWrap :error="form.errors.title">
            <Label for="title">{{ __('Title') }}</Label>
            <Input v-model="form.title" id="title" />
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
          <InputWrap>
            <Label for="due_at">{{ __('Due date') }}</Label>
            <div class="grid grid-cols-2 gap-6">
              <DatePicker
                v-model="form.due_at"
                color="pink"
                :is-dark="isDark"
                mode="dateTime"
                is-expanded
              />
              <div>
                <HelpText>
                  {{ __('Set the date and time that this invoice is due.') }}
                </HelpText>
              </div>
            </div>
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
                {{ term.school_years }} {{ term.name }}
              </option>
            </Select>
            <HelpText>{{ __('Associating a term with an invoice offers benefits but I am not sure what those benefits are yet.') }}</HelpText>
          </InputWrap>
        </Fieldset>
      </div>

      <div class="pt-8">
        <div class="mb-6">
          <CardSectionHeader>
            {{ __('Invoice line items') }}
          </CardSectionHeader>
          <HelpText class="text-sm mt-1">
            {{ __('Add line items to the build the invoice and total receivable amount.') }}
          </HelpText>
        </div>

        <ul class="space-y-3 py-3">
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
              class="bg-gray-100 dark:bg-gray-800 shadow overflow-hidden rounded-md px-6 py-4"
            >
              <Fieldset>
                <InputWrap>
                  <Label :for="`fee_id_${index}`">{{ __('Fee') }}</Label>
                  <Select
                    v-model="item.fee_id" :id="`fee_id_${index}`"
                    @change="feeSelected(item)"
                  >
                    <option :value="null">{{ __('Use a custom amount') }}</option>
                    <option
                      v-for="fee in fees"
                      :key="fee.id"
                      :value="fee.id"
                    >
                      {{ fee.name }}{{ fee.code ? ` (${fee.code})` : '' }} - {{ fee.amount_formatted }}
                    </option>
                  </Select>
                  <HelpText>
                    {{ __("Associating line items with a fee will help with reporting, but isn't required.") }}
                  </HelpText>
                </InputWrap>
                <InputWrap>
                  <Label :for="`amount_${index}`">{{ __('Amount') }}</Label>
                  <Input v-model="item.amount" :id="`amount_${index}`" type="number" />
                  <HelpText v-html="__('The amount should be in the smallest units possible for your currency, such as cents. This amount will be displayed as <strong>:amount</strong>', { amount: displayCurrency(item.amount) })" />
                </InputWrap>
                <InputWrap>
                  <Label :for="`quantity_${index}`">{{ __('Quantity') }}</Label>
                  <Input v-model="item.quantity" :id="`quantity_${index}`" type="number" />
                </InputWrap>

                <div class="flex justify-between items-center">
                  <h4 class="font-bold">
                    {{ __('Line item total: :total', { total: displayCurrency(item.amount * item.quantity) }) }}
                  </h4>
                  <Button color="red" size="sm" type="button" @click.prevent="form.items.splice(index, 1)">
                    <TrashIcon class="w-4 h-4" />
                    <span class="ml-2">{{ __('Remove line item') }}</span>
                  </Button>
                </div>
              </Fieldset>
            </li>
          </TransitionGroup>
        </ul>

        <div class="relative">
          <div class="absolute inset-0 flex items-center" aria-hidden="true">
            <div class="w-full border-t border-gray-300 dark:border-gray-400" />
          </div>
          <div class="relative flex justify-center">
            <button @click.prevent="addInvoiceLineItem" type="button" class="inline-flex items-center shadow-sm px-4 py-1.5 border border-gray-300 dark:border-gray-600 text-sm leading-5 font-medium rounded-full text-gray-700 dark:text-gray-100 bg-white dark:bg-gray-500 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
              <PlusSmIcon class="-ml-1.5 mr-1 h-5 w-5 text-gray-400 dark:text-gray-200" aria-hidden="true" />
              <span>{{ __('Add invoice line item') }}</span>
            </button>
          </div>
        </div>
      </div>

      <div class="pt-8">
        <div class="mb-6">
          <CardSectionHeader>
            {{ __('Summary') }}
          </CardSectionHeader>
          <HelpText class="text-sm mt-1">
            {{ __('Below is the summary of the invoice, including all relevant details that will be displayed when viewing the invoice.') }}
          </HelpText>
        </div>

        <dl class="sm:divide-y sm:divide-gray-200 dark:sm:divide-gray-500">
          <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">
              {{ __('Title') }}
            </dt>
            <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
              {{ form.title }}
            </dd>
          </div>
          <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">
              {{ __('Description') }}
            </dt>
            <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
              {{ form.description }}
            </dd>
          </div>
          <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">
              {{ __('Due date') }}
            </dt>
            <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
              {{ dueDate }}
            </dd>
          </div>
        </dl>
      </div>
    </FormMultipartWrapper>
  </Slideout>
</template>

<script>
import { computed, inject, ref } from 'vue'
import { useForm, usePage } from '@inertiajs/inertia-vue3'
import { PlusSmIcon } from '@heroicons/vue/solid'
import { TrashIcon } from '@heroicons/vue/outline'
import Fieldset from '../forms/Fieldset'
import Slideout from '../Slideout'
import CardHeader from '../CardHeader'
import HelpText from '../HelpText'
import InputWrap from '../forms/InputWrap'
import Checkbox from '../forms/Checkbox'
import Label from '../forms/Label'
import CheckboxText from '../forms/CheckboxText'
import CheckboxWrapper from '../forms/CheckboxWrapper'
import fetchesTerms from '../../composition/fetchesTerms'
import Select from '../forms/Select'
import Textarea from '../forms/Textarea'
import { nanoid } from 'nanoid'
import displaysCurrency from '../../composition/displaysCurrency'
import fetchesFees from '../../composition/fetchesFees'
import Input from '../forms/Input'
import Button from '../Button'
import FormMultipartWrapper from '../forms/FormMultipartWrapper'
import CardSectionHeader from '../CardSectionHeader'
import { Calendar, DatePicker } from 'v-calendar'
import dayjs from '../../plugins/dayjs'

export default {
  components: {
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
    Slideout,
    Fieldset,
    Label,
    PlusSmIcon,
    TrashIcon,
    Calendar,
    DatePicker,
  },
  props: {
    student: Object,
  },
  emits: ['close'],

  setup (props) {
    const $route = inject('$route')
    const $http = inject('$http')
    const { terms } = fetchesTerms()
    const { fees } = fetchesFees()
    const page = usePage()
    const isDark = computed(() => window.isDark)
    const form = useForm({
      title: null,
      description: null,
      term_id: null,
      due_at: null,
      items: []
    })

    const school = computed(() => page.props.value.school)
    const dueDate = computed(() => {
      return form.due_at
        ? dayjs(form.due_at).tz(page.props.value.user.timezone || 'UTC').format('MMMM D, YYYY H:mm')
        : ''
    })
    const saving = ref(false)
    const { displayCurrency } = displaysCurrency()

    const saveInvoice = () => {
      saving.value = true

      form.post($route('students.invoices.store', [props.student]), {
        preserveScroll: true,
        onFinish () {
          saving.value = false
        }
      })
    }
    const addInvoiceLineItem = () => {
      form.items.push({
        id: nanoid(),
        fee_id: null,
        amount: null,
        quantity: 1,
      })
    }
    const feeSelected = item => {
      // find the fee to get the amount
      const fee = fees.value.find(f => f.id === item.fee_id)

      // set the items amount
      if (fee) {
        item.amount = fee.amount
      }
    }

    return {
      school,
      saving,
      terms,
      fees,
      form,
      saveInvoice,
      addInvoiceLineItem,
      displayCurrency,
      feeSelected,
      isDark,
      dueDate,
    }
  },
}
</script>
