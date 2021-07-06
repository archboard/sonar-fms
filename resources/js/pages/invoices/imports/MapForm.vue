<template>
  <form class="xl:col-span-3" @submit.prevent="saveImport">
    <Alert v-if="form.hasErrors" level="error" class="mb-8">
      {{ __('Please correct the errors below and try again.') }}
    </Alert>

    <FormMultipartWrapper>
      <!-- Student mapping settings -->
      <div>
        <div class="mb-6">
          <CardSectionHeader>{{ __('Student identity settings') }}</CardSectionHeader>
          <HelpText>
            {{ __('Configure how students are found and student import behavior.') }}
          </HelpText>
        </div>
        <Fieldset>
          <InputWrap :error="form.errors.student_attribute">
            <Label for="title" :required="true">{{ __('Student identity field') }}</Label>
            <Select v-model="form.student_attribute" id="student_attribute">
              <option :value="null" disabled selected>{{ __('Select a student field') }}</option>
              <option value="sis_id">{{ __('SIS ID (DCID)') }}</option>
              <option value="student_number">{{ __('Student number') }}</option>
              <option value="email">{{ __('Email') }}</option>
            </Select>
            <HelpText>
              {{ __('Select the field by which a student can be uniquely identified.') }}
            </HelpText>
          </InputWrap>

          <InputWrap :error="form.errors.student_column">
            <Label for="title" :required="true">{{ __('Student reference column') }}</Label>
            <ColumnSelector v-model="form.student_column" id="student_column" :headers="headers" />
            <HelpText>
              {{ __('Select the column that holds student identifying data.') }}
            </HelpText>
          </InputWrap>

          <!-- Not sure if we want this behavior -->
<!--          <InputWrap>-->
<!--            <CheckboxWrapper>-->
<!--              <Checkbox v-model:checked="form.create_new_students" />-->
<!--              <CheckboxText>{{ __('Create new students has blank identifying value') }}</CheckboxText>-->
<!--            </CheckboxWrapper>-->
<!--            <HelpText>-->
<!--              {{ __("Enabling this option will attempt to create a new student record if a row's value is empty for the student reference column.") }}-->
<!--            </HelpText>-->
<!--          </InputWrap>-->
        </Fieldset>
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
            <MapField v-model="form.title" :headers="headers" id="title">
              <Input v-model="form.title.value" id="title" :placeholder="__('Invoice title')" />
              <template v-slot:after>
                <HelpText>
                  {{ __('Give the invoice a meaningful title that is easily recognizable and descriptive.') }}
                </HelpText>
              </template>
            </MapField>
          </InputWrap>

          <InputWrap>
            <Label for="description">{{ __('Description') }}</Label>
            <MapField v-model="form.description" :headers="headers" id="description">
              <Textarea v-model="form.description.value" id="description" />
              <template v-slot:after>
                <HelpText>
                  {{ __('This is a description of the invoice that will be displayed with the invoice.') }}
                </HelpText>
              </template>
            </MapField>
          </InputWrap>

          <InputWrap :error="form.errors.available_at">
            <Label for="available_at">{{ __('Availability') }}</Label>
            <MapField v-model="form.available_at" :headers="headers" id="available_at">
              <DatePicker v-model="form.available_at.value" id="available_at" />
              <template v-slot:after>
                <HelpText>
                  {{ __("Set a date and time that this invoice is available to the student's guardians or other contacts. Before the configured time, it will only be viewable to admins. This is helpful to use if you want to prepare and preview invoices before actually making them available for the student. The time is based on your current timezone of :timezone. If this timezone is incorrect you can change it in your Personal Settings.", { timezone }) }}
                </HelpText>
              </template>
            </MapField>
          </InputWrap>

          <InputWrap :error="form.errors.due_at">
            <Label for="due_at">{{ __('Due date') }}</Label>
            <MapField v-model="form.due_at" :headers="headers" id="due_at">
              <DatePicker v-model="form.due_at.value" id="due_at" />
              <template v-slot:after>
                <HelpText>
                  {{ __("Set the date and time that this invoice is due, or don't set one to not have a due date. The time is based on your current timezone of :timezone. If this timezone is incorrect you can change it in your Personal Settings.", { timezone }) }}
                </HelpText>
              </template>
            </MapField>
          </InputWrap>

          <InputWrap>
            <Label for="term_id">{{ __('Term') }}</Label>
            <MapField v-model="form.term_id" :headers="headers" id="term_id">
              <Select v-model="form.term_id.value" id="term_id">
                <option :value="null">{{ __('No term') }}</option>
                <option
                  v-for="term in terms"
                  :key="term.id"
                  :value="term.id"
                >
                  {{ term.school_years }} - {{ term.name }}
                </option>
              </Select>
              <template v-slot:after>
                <HelpText>
                  {{ __('Associating a term with an invoice allows you to group invoices by school term and offers another reporting perspective.') }}
                </HelpText>
              </template>
            </MapField>
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
                      <MapField v-model="item.fee_id" :headers="headers" :id="`fee_id_${index}`">
                        <Select
                          v-model="item.fee_id.value" :id="`fee_id_${index}`"
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
                        <template v-slot:after>
                          <HelpText>
                            {{ __("Associating line items with a fee will help with reporting and syncing data, but isn't required.") }}
                          </HelpText>
                        </template>
                      </MapField>
                    </InputWrap>

                    <InputWrap :error="form.errors[`items.${index}.name`]">
                      <Label :for="`name_${index}`" :required="true">{{ __('Name') }}</Label>
                      <MapField v-model="item.name" :headers="headers" :id="`name_${index}`">
                        <Input v-model="item.name.value" :id="`name_${index}`" />
                        <template v-slot:after>
                          <HelpText>
                            {{ __('This is the label given to the line item and will be displayed on the invoice.') }}
                          </HelpText>
                        </template>
                      </MapField>
                    </InputWrap>

                    <InputWrap :error="form.errors[`items.${index}.amount_per_unit`]">
                      <Label :for="`amount_per_unit_${index}`" :required="true">{{ __('Amount per unit') }}</Label>
                      <MapField v-model="item.amount_per_unit" :headers="headers" :id="`amount_per_unit_${index}`">
                        <CurrencyInput v-model="item.amount_per_unit.value" :id="`amount_per_unit_${index}`" />
                      </MapField>
                    </InputWrap>

                    <InputWrap :error="form.errors[`items.${index}.quantity`]">
                      <Label :for="`quantity_${index}`" :required="true">{{ __('Quantity') }}</Label>
                      <MapField v-model="item.quantity" :headers="headers" :id="`quantity_${index}`">
                        <Input v-model="item.quantity.value" :id="`quantity_${index}`" type="number" />
                      </MapField>
                    </InputWrap>

                    <div class="flex justify-end">
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

<!--        <FadeIn>-->
<!--          <CardWrapper v-if="form.items.length > 0" class="my-4">-->
<!--            <CardPadding>-->
<!--              <div class="flex justify-between">-->
<!--                <h4 class="font-bold">-->
<!--                  {{ __('Invoice subtotal') }}-->
<!--                </h4>-->
<!--                <div class="font-bold">-->
<!--                  {{ displayCurrency(subtotal) }}-->
<!--                </div>-->
<!--              </div>-->
<!--            </CardPadding>-->
<!--          </CardWrapper>-->
<!--        </FadeIn>-->

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
                      <MapField v-model="item.scholarship_id" :headers="headers" :id="`scholarship_id_${index}`">
                        <Select
                          v-model="item.scholarship_id.value" :id="`scholarship_id_${index}`"
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
                        <template v-slot:after>
                          <HelpText>
                            {{ __("Associating a scholarship will help with reporting and syncing data, but isn't required.") }}
                          </HelpText>
                        </template>
                      </MapField>
                    </InputWrap>

                    <InputWrap :error="form.errors[`scholarships.${index}.name`]">
                      <Label :for="`scholarship_name_${index}`" :required="true">{{ __('Name') }}</Label>
                      <MapField v-model="item.name" :headers="headers" :id="`scholarship_name_${index}`">
                        <Input v-model="item.name.value" :id="`scholarship_name_${index}`" :placeholder="__('Scholarship name')" />
                        <template v-slot:after>
                          <HelpText>
                            {{ __('This is the label given to the line item and will be displayed on the invoice.') }}
                          </HelpText>
                        </template>
                      </MapField>
                    </InputWrap>

                    <InputWrap>
                      <RadioGroup>
                        <RadioWrapper>
                          <Radio v-model:checked="item.use_amount" :value="true" />
                          <CheckboxText>
                            {{ __('Use an amount') }}
                          </CheckboxText>
                        </RadioWrapper>
                        <RadioWrapper>
                          <Radio v-model:checked="item.use_amount" :value="false" />
                          <CheckboxText>
                            {{ __('Use a percentage') }}
                          </CheckboxText>
                        </RadioWrapper>
                      </RadioGroup>
                    </InputWrap>

                    <InputWrap v-if="item.use_amount" :error="form.errors[`scholarships.${index}.amount`]">
                      <Label :for="`scholarship_amount_${index}`">{{ __('Amount') }}</Label>
                      <MapField v-model="item.amount" :headers="headers" :id="`scholarship_amount_${index}`">
                        <CurrencyInput v-model="item.amount.value" :id="`scholarship_amount_${index}`" />
                      </MapField>
                    </InputWrap>

                    <InputWrap v-else :error="form.errors[`scholarships.${index}.percentage`]">
                      <Label :for="`scholarship_percentage_${index}`" :required="true">{{ __('Percentage') }}</Label>
                      <MapField v-model="item.percentage" :headers="headers" :id="`scholarship_percentage_${index}`">
                        <Input v-model="item.percentage.value" :id="`scholarship_percentage_${index}`" />
                        <template v-slot:after>
                          <HelpText>
                            {{ __('This is the default scholarship percentage that will be applied to the invoice. This value is the percentage of the total invoice amount that has been deducted from the invoice. [invoice total] - ([invoice total] * [scholarship percentage]) = [total with scholarship applied].') }}
                          </HelpText>
                        </template>
                      </MapField>
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
                            <CheckboxText>
                              <span v-if="lineItem.name.isManual">
                                {{ lineItem.name.value }}
                              </span>
                              <span v-else class="flex items-center space-x-1">
                                <LinkIcon class="w-4 h-4" />
                                <span>{{ lineItem.name.column }}</span>
                              </span>
                            </CheckboxText>
                          </CheckboxWrapper>
                        </div>
                      </div>
                    </InputWrap>

                    <div class="flex justify-end">
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
                        <InputWrap>
                          <RadioGroup>
                            <RadioWrapper>
                              <Radio v-model:checked="term.use_amount" :value="true" />
                              <CheckboxText>
                                {{ __('Use an amount') }}
                              </CheckboxText>
                            </RadioWrapper>
                            <RadioWrapper>
                              <Radio v-model:checked="term.use_amount" :value="false" />
                              <CheckboxText>
                                {{ __('Use a percentage') }}
                              </CheckboxText>
                            </RadioWrapper>
                          </RadioGroup>
                        </InputWrap>

                        <InputWrap v-if="term.use_amount" :error="form.errors[`payment_schedules.${index}.terms.${termIndex}.amount`]">
                          <Label :required="true" :for="`schedule_${index}_${termIndex}_amount`">{{ __('Amount') }}</Label>
                          <MapField v-model="term.amount" :headers="headers" :id="`schedule_${index}_${termIndex}_amount`">
                            <CurrencyInput v-model="term.amount.value" :id="`schedule_${index}_${termIndex}_amount`" />
                          </MapField>
                        </InputWrap>

                        <InputWrap v-else :error="form.errors[`payment_schedules.${index}.terms.${termIndex}.percentage`]">
                          <Label :for="`scholarship_percentage_${index}`" :required="true">{{ __('Percentage') }}</Label>
                          <MapField v-model="term.percentage" :headers="headers" :id="`schedule_${index}_${termIndex}_percentage`">
                            <Input v-model="term.percentage.value" :id="`schedule_${index}_${termIndex}_percentage`" />
                          </MapField>
                        </InputWrap>

                        <InputWrap :error="form.errors[`payment_schedules.${index}.terms.${termIndex}.due_at`]">
                          <Label :for="`schedule_${index}_${termIndex}_due_at`">{{ __('Due date') }}</Label>
                          <MapField v-model="term.due_at" :headers="headers" :id="`schedule_${index}_${termIndex}_due_at`">
                            <DatePicker :id="`schedule_${index}_${termIndex}_due_at`" v-model="term.due_at.value" />
                          </MapField>
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
                <li class="px-2 w-full sm:w-1/2 md:w-full lg:w-1/2 xl:w-1/3 relative">
                  <div class="opacity-50 rounded-md border border-gray-200 bg-gray-200 dark:bg-gray-800 dark:border-gray-500 p-3">
                    <Fieldset>
                      <InputWrap>
                        <RadioGroup>
                          <Mocker>
                            <RadioWrapper>
                              <Radio />
                              <CheckboxText>&nbsp;</CheckboxText>
                            </RadioWrapper>
                          </Mocker>
                          <Mocker>
                            <RadioWrapper>
                              <Radio />
                              <CheckboxText>&nbsp;</CheckboxText>
                            </RadioWrapper>
                          </Mocker>
                        </RadioGroup>
                      </InputWrap>

                      <InputWrap>
                        <Mocker :inline="true">
                          <Label>&nbsp;</Label>
                        </Mocker>
                        <Mocker>
                          <CurrencyInput />
                        </Mocker>
                        <Mocker>
                          <div class="text-sm mt-1">&nbsp;</div>
                        </Mocker>
                      </InputWrap>

                      <InputWrap>
                        <Mocker :inline="true">
                          <Label>&nbsp;</Label>
                        </Mocker>
                        <Mocker>
                          <Input />
                        </Mocker>
                        <Mocker>
                          <div class="text-sm mt-1">&nbsp;</div>
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

              <div class="flex justify-end pt-6">
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

    <div class="mt-8 p-4 border-t border-gray-400 bg-gray-200 dark:bg-gray-700 dark:border-gray-300 rounded-b-md">
      <Button type="submit" size="lg" :loading="form.processing">
        {{ __('Save mapping') }}
      </Button>
    </div>
  </form>

</template>

<script>
import { computed, inject, ref, watch, watchEffect } from 'vue'
import { useForm, usePage } from '@inertiajs/inertia-vue3'
import { PlusSmIcon } from '@heroicons/vue/solid'
import { TrashIcon, LinkIcon } from '@heroicons/vue/outline'
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
import FadeIn from '@/components/transitions/FadeIn'
import Error from '@/components/forms/Error'
import DatePicker from '@/components/forms/DatePicker'
import Alert from '@/components/Alert'
import displaysDate from '@/composition/displaysDate'
import invoiceImportItemForm from '@/composition/invoiceImportItemForm'
import invoiceImportScholarshipForm from '@/composition/invoiceImportScholarshipForm'
import invoicePaymentScheduleForm from '@/composition/invoicePaymentScheduleForm'
import CurrencyInput from '@/components/forms/CurrencyInput'
import CardWrapper from '@/components/CardWrapper'
import CardPadding from '@/components/CardPadding'
import InvoiceSummary from '@/components/InvoiceSummary'
import Mocker from '@/components/Mocker'
import Modal from '@/components/Modal'
import invoiceImportMapField from '@/composition/invoiceImportMapField'
import ColumnSelector from '@/components/forms/ColumnSelector'
import MapField from '@/components/forms/MapField'
import Radio from '@/components/forms/Radio'
import RadioGroup from '@/components/forms/RadioGroup'
import RadioWrapper from '@/components/forms/RadioWrapper'
import invoiceImportPaymentScheduleForm from '@/composition/invoiceImportPaymentScheduleForm'

export default {
  components: {
    RadioWrapper,
    Radio,
    RadioGroup,
    MapField,
    ColumnSelector,
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
    LinkIcon,
    DatePicker,
  },
  props: {
    invoiceTemplate: {
      type: Object,
      default: () => ({})
    },
    invoiceForm: {
      type: Object,
      default: () => ({})
    },
    headers: {
      type: Array,
      required: true,
    },
    invoiceImport: {
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
    const { addMapFieldValue } = invoiceImportMapField()
    const page = usePage()
    const isDark = computed(() => window.isDark)
    const form = useForm({
      student_attribute: props.invoiceImport.mapping?.student_attribute || null,
      student_column: props.invoiceImport.mapping?.student_column || null,
      create_new_students: props.invoiceImport.mapping?.create_new_students || false,
      title: props.invoiceImport.mapping?.title || addMapFieldValue(),
      description: props.invoiceImport.mapping?.description || addMapFieldValue(),
      term_id: props.invoiceImport.mapping?.term_id || addMapFieldValue(),
      available_at: props.invoiceImport.mapping?.available_at || addMapFieldValue(),
      due_at: props.invoiceImport.mapping?.due_at || addMapFieldValue(),
      notify: props.invoiceImport.mapping?.notify || addMapFieldValue(false),
      items: props.invoiceImport.mapping?.items || [],
      scholarships: props.invoiceImport.mapping?.scholarships || [],
      payment_schedules: props.invoiceImport.mapping?.payment_schedules || [],
    })
    // Emit the initial value
    emit('update:invoiceForm', form)

    const school = computed(() => page.props.value.school)

    const { timezone, displayDate } = displaysDate()
    const { displayCurrency } = displaysCurrency()

    const saveImport = () => {
      form.put($route('invoices.imports.map'))
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

    watch(() => form, (state) => {
      emit('update:invoiceForm', state)
    }, { deep: true })

    // Invoice line items
    const {
      fees,
      addInvoiceLineItem,
      feeSelected
    } = invoiceImportItemForm(form)

    // Scholarships
    const {
      scholarships,
      addScholarship,
      scholarshipSelected
    } = invoiceImportScholarshipForm(form)

    // Payment schedules
    const {
      removePaymentTerm,
    } = invoicePaymentScheduleForm(form, 0)
    const {
      addPaymentTerm,
      addPaymentSchedule
    } = invoiceImportPaymentScheduleForm(form)

    // Add an initial line item
    if (form.items.length === 0) {
      addInvoiceLineItem()
    }

    return {
      reviewing,
      school,
      terms,
      fees,
      form,
      saveImport,
      addInvoiceLineItem,
      displayCurrency,
      feeSelected,
      isDark,
      displayDate,
      timezone,
      strategies,
      scholarships,
      addScholarship,
      scholarshipSelected,
      addPaymentSchedule,
      addPaymentTerm,
      removePaymentTerm,
    }
  },
}
</script>
