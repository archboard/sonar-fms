<template>
  <Slideout
    @close="$emit('close')"
    @action="saveInvoice"
    :auto-close="false"
    :processing="form.processing"
  >
    <template v-slot:header>
      <div class="space-y-1">
        <CardHeader v-if="isNew">
          {{ __('New invoice for :name', { name: student.full_name }) }}
        </CardHeader>
        <CardHeader v-else>
          {{ __('Update invoice for :name', { name: student.full_name }) }}
        </CardHeader>
        <HelpText v-if="isNew">
          {{ __('Create a new invoice by providing the following details.') }}
        </HelpText>
        <HelpText v-else>
          {{ __('Modify invoice details.') }}
        </HelpText>
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

          <InputWrap :error="form.errors.due_at">
            <Label for="due_at">{{ __('Due date') }}</Label>
            <div class="grid grid-cols-2 gap-6">
              <DatePicker
                v-model="form.due_at"
                color="pink"
                :is-dark="isDark"
                mode="dateTime"
                :minute-increment="15"
                is-expanded
                :model-config="{ timeAdjust: '00:00:00' }"
              />
              <div>
                <HelpText>
                  {{ __("Set the date and time that this invoice is due, or don't set one to not have a due date. The time is based on your current timezone of :timezone. If this is incorrect you can change it in your Personal Settings.", { timezone }) }}
                </HelpText>
                <FadeIn>
                  <div class="mt-4" v-show="form.due_at">
                    <Button size="sm" type="button" @click.prevent="form.due_at = null">
                      {{ __('Remove') }}
                    </Button>
                  </div>
                </FadeIn>
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

                <InputWrap v-if="item.fee_id">
                  <CheckboxWrapper>
                    <Checkbox v-model:checked="item.sync_with_fee" @change="itemSyncChanged(item)" />
                    <CheckboxText>{{ __('Sync title and amount with associated fee') }}</CheckboxText>
                  </CheckboxWrapper>
                  <HelpText>
                    {{ __("This option will keep the line item name and amount in sync with the name and amount of the underlying fee. This means that if you change the fee's name or amount, this line item will reflect those changes. If it is not enabled, the title and amount set below will be set unless changed manually later.") }}
                  </HelpText>
                </InputWrap>

                <InputWrap v-if="!item.sync_with_fee" :error="form.errors[`items.${index}.name`]">
                  <Label :for="`name_${index}`" :required="true">{{ __('Name') }}</Label>
                  <Input v-model="item.name" :id="`name_${index}`" />
                  <HelpText>
                    {{ __('This is the label given to the line item and will be displayed on the invoice.') }}
                  </HelpText>
                </InputWrap>

                <InputWrap v-if="!item.sync_with_fee" :error="form.errors[`items.${index}.amount_per_unit`]">
                  <Label :for="`amount_per_unit_${index}`" :required="true">{{ __('Amount per unit') }}</Label>
                  <Input v-model="item.amount_per_unit" :id="`amount_per_unit_${index}`" type="number" />
                  <HelpText v-html="__('The amount should be in the smallest units possible for your currency, such as cents. This amount will be displayed as <strong>:amount</strong>', { amount: displayCurrency(item.amount_per_unit) })" />
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
              v-for="(item, index) in form.scholarships"
              :key="item.id"
              class="bg-gray-100 dark:bg-gray-800 shadow overflow-hidden rounded-md px-6 py-4"
            >
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
                  <Input v-model="item.amount" :id="`scholarship_amount_${index}`" type="number" />
                  <HelpText v-html="__('The amount should be in the smallest units possible for your currency, such as cents. This amount will be displayed as <strong>:amount</strong>', { amount: displayCurrency(item.amount_per_unit) })" />
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
            </li>
          </TransitionGroup>
        </ul>

        <div class="relative">
          <div class="absolute inset-0 flex items-center" aria-hidden="true">
            <div class="w-full border-t border-gray-300 dark:border-gray-400" />
          </div>
          <div class="relative flex justify-center">
            <button @click.prevent="addScholarship" type="button" class="inline-flex items-center shadow-sm px-4 py-1.5 border border-gray-300 dark:border-gray-600 text-sm leading-5 font-medium rounded-full text-gray-700 dark:text-gray-100 bg-white dark:bg-gray-500 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
              <PlusSmIcon class="-ml-1.5 mr-1 h-5 w-5 text-gray-400 dark:text-gray-200" aria-hidden="true" />
              <span>{{ __('Add scholarship') }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Summary -->
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
              <span v-if="dueDate" class="flex items-end">
                <span>{{ dueDate }}</span>
                <span class="inline-flex ml-3">
                  <button class="text-gray-500 dark:text-gray-300 hover:underline focus:outline-none" type="button" @click.prevent="form.due_at = null">
                    {{ __('Remove') }}
                  </button>
                </span>
              </span>
              <span v-else>
                {{ __('No due date.') }}
              </span>
            </dd>
          </div>
          <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">
              {{ __('Notification') }}
            </dt>
            <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
              <span v-if="form.notify">
                {{ __('Contacts will be notified in 15 minutes, unless cancelled.') }}
              </span>
              <span v-else>
                {{ __('Manually notify.') }}
              </span>
            </dd>
          </div>
          <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">
              {{ __('Line items') }}
            </dt>
            <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
              <div
                v-for="item in form.items"
                :key="item.id"
                class="flex justify-between py-1"
              >
                <div>
                  {{ __(':name x :quantity', { ...item }) }}
                </div>
                <div>
                  {{ displayCurrency(item.amount_per_unit * item.quantity) }}
                </div>
              </div>
              <div class="mt-2 pt-2 flex justify-between font-bold border-t border-gray-200 dark:border-gray-400">
                <div>{{ __('Total' )}}</div>
                <div>{{ displayCurrency(itemsTotal) }}</div>
              </div>
            </dd>
          </div>
          <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">
              {{ __('Scholarships') }}
            </dt>
            <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
              <div
                v-for="item in form.scholarships"
                :key="item.id"
                class="flex justify-between py-1"
              >
                <div>
                  {{ item.name }}
                </div>
                <div>
                  {{ displayCurrency(getItemDiscount(item)) }}
                </div>
              </div>
              <div
                class="font-bold flex justify-between"
                :class="{
                  'mt-2 pt-2 border-t border-gray-200 dark:border-gray-400': form.scholarships.length > 0
                }"
              >
                <div>{{ __('Discount total' )}}</div>
                <div>{{ displayCurrency(discountTotal) }}</div>
              </div>
            </dd>
          </div>
          <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
            <dt class="text-sm font-medium">
              <strong>{{ __('Total due') }}</strong>
            </dt>
            <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2 text-right">
              <strong>{{ totalDue }}</strong>
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
import fetchesScholarships from '../../composition/fetchesScholarships'
import fetchesResolutionStrategies from '../../composition/fetchesResolutionStrategies'
import Input from '../forms/Input'
import Button from '../Button'
import FormMultipartWrapper from '../forms/FormMultipartWrapper'
import CardSectionHeader from '../CardSectionHeader'
import { Calendar, DatePicker } from 'v-calendar'
import dayjs from '../../plugins/dayjs'
import FadeIn from '../transitions/FadeIn'
import Error from '../forms/Error'

export default {
  components: {
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
    invoice: {
      type: Object,
      default: () => ({})
    }
  },
  emits: ['close'],

  setup (props) {
    const $route = inject('$route')
    const { terms } = fetchesTerms()
    const { fees } = fetchesFees()
    const { scholarships } = fetchesScholarships()
    const { strategies } = fetchesResolutionStrategies()
    const isNew = computed(() => !props.invoice.id)
    const page = usePage()
    const isDark = computed(() => window.isDark)
    const form = useForm({
      title: props.invoice.title || null,
      description: props.invoice.description || null,
      term_id: props.invoice.term_id || null,
      due_at: props.invoice.due_at || null,
      notify: props.invoice.notify || false,
      items: props.invoice.items || [],
      scholarships: props.invoice.scholarships || [],
    })

    const school = computed(() => page.props.value.school)
    const timezone = computed(() => page.props.value.user?.timezone || 'UTC')
    const dueDate = computed(() => {
      return form.due_at
        ? dayjs(form.due_at).tz(timezone.value).format('MMMM D, YYYY H:mm')
        : ''
    })
    const { displayCurrency } = displaysCurrency()
    const getItemDiscount = item => {
      let discount = item.amount || 0
      const percentage = item.percentage || 0
      let percentageDiscount = itemsTotal.value * (percentage / 100)

      if (discount && percentage) {
        discount = item.resolution_strategy.includes('Least')
          ? Math.min(discount, percentageDiscount)
          : Math.max(discount, percentageDiscount)
      }

      if (!discount && percentageDiscount) {
        discount = percentageDiscount
      }

      return parseFloat(discount)
    }

    const itemsTotal = computed(() => form.items.reduce((total, i) => total + (i.amount_per_unit * i.quantity), 0))
    const discountTotal = computed(() => form.scholarships.reduce((total, i) => total + getItemDiscount(i), 0))
    const totalDue = computed(() => {
      let total = itemsTotal.value - discountTotal.value

      if (total < 0) {
        total = 0
      }

      return displayCurrency(total)
    })

    const saveInvoice = close => {
      const route = props.invoice.id
        ? $route('students.invoices.update', [props.student, props.invoice])
        : $route('students.invoices.store', [props.student])
      const method = props.invoice.id
        ? 'put'
        : 'post'

      form[method](route, {
        preserveScroll: true,
        onSuccess () {
          close()
        }
      })
    }

    // Invoice line items
    const addInvoiceLineItem = () => {
      form.items.push({
        id: nanoid(),
        fee_id: null,
        sync_with_fee: false,
        name: null,
        amount_per_unit: null,
        quantity: 1,
      })
    }
    const syncItemWithFee = item => {
      const fee = fees.value.find(f => f.id === item.fee_id)

      if (fee) {
        item.name = fee.name
        item.amount_per_unit = fee.amount
      }
    }
    const feeSelected = item => {
      syncItemWithFee(item)
      item.sync_with_fee = false
    }
    const itemSyncChanged = item => {
      if (item.sync_with_fee) {
        syncItemWithFee(item)
      }
    }

    // Scholarships
    const addScholarship = () => {
      form.scholarships.push({
        id: nanoid(),
        scholarship_id: null,
        sync_with_scholarship: false,
        name: null,
        amount: null,
        percentage: null,
        resolution_strategy: 'App\\ResolutionStrategies\\Least',
      })
    }
    const syncWithScholarship = item => {
      const scholarship = scholarships.value.find(s => s.id === item.scholarship_id)

      if (scholarship) {
        item.name = scholarship.name
        item.amount = scholarship.amount
        item.percentage = scholarship.percentage
        item.resolution_strategy = scholarship.resolution_strategy
      }
    }
    const scholarshipSelected = item => {
      syncWithScholarship(item)
      item.sync_with_scholarship = false
    }
    const scholarshipSyncChanged = item => {
      if (item.sync_with_scholarship) {
        syncWithScholarship(item)
      }
    }

    return {
      isNew,
      school,
      terms,
      fees,
      form,
      saveInvoice,
      addInvoiceLineItem,
      displayCurrency,
      feeSelected,
      isDark,
      dueDate,
      timezone,
      getItemDiscount,
      itemsTotal,
      discountTotal,
      totalDue,
      itemSyncChanged,
      strategies,
      scholarships,
      addScholarship,
      scholarshipSelected,
      scholarshipSyncChanged,
    }
  },
}
</script>
