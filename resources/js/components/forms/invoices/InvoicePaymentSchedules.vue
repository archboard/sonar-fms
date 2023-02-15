<template>
  <div class="mb-6">
    <CardSectionHeader>
      {{ __('Payment schedules') }}
    </CardSectionHeader>
    <HelpText class="text-sm mt-1">
      {{ __('Add available payment schedules to allow the invoice to be paid in separate payments rather than all at once.') }}
    </HelpText>
  </div>

  <ul class="space-y-3">
    <FadeInGroup>
      <li
        v-for="(item, index) in form.payment_schedules"
        :key="item.id"
        class="bg-gray-100 dark:bg-gray-800 shadow overflow-hidden rounded-md p-6"
      >
        <ul class="flex flex-wrap -mx-2">
          <FadeInGroup>
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
          </FadeInGroup>

          <!-- Mock term that just has the button -->
          <li class="px-2 w-full sm:w-1/2 md:w-full lg:w-1/2 xl:w-1/3 relative">
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
    </FadeInGroup>
  </ul>

  <AddThingButton @click="addPaymentSchedule">
    {{ __('Add payment schedule') }}
  </AddThingButton>
</template>

<script>
import { defineComponent } from 'vue'
import invoicePaymentScheduleForm from '@/composition/invoicePaymentScheduleForm.js'
import invoiceFormComponent from '@/composition/invoiceFormComponent.js'
import InvoiceFormCollection from '@/mixins/InvoiceFormCollection'
import Mocker from '@/components/Mocker.vue'
import DatePicker from '@/components/forms/DatePicker.vue'
import AddThingButton from '@/components/forms/AddThingButton.vue'

export default defineComponent({
  mixins: [InvoiceFormCollection],
  components: {
    AddThingButton,
    DatePicker,
    Mocker,
  },
  props: {
    total: Number,
  },

  setup (props, context) {
    const { localValue, displayCurrency } = invoiceFormComponent(props, context)
    const {
      addPaymentSchedule,
      addPaymentTerm,
      getScheduleTotal,
      removePaymentTerm,
    } = invoicePaymentScheduleForm(props.form, props.total)

    return {
      localValue,
      displayCurrency,
      addPaymentSchedule,
      addPaymentTerm,
      getScheduleTotal,
      removePaymentTerm,
    }
  }
})
</script>
