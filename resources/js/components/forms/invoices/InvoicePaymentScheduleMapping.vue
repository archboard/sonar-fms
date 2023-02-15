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
        v-for="(item, index) in localValue"
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
          </FadeInGroup>

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
          <Button color="red" size="sm" type="button" @click.prevent="localValue.splice(index, 1)">
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
import Error from '@/components/forms/Error.vue'
import InvoiceMappingFormCollection from '@/mixins/InvoiceMappingFormCollection'
import hasModelValue from '@/composition/hasModelValue.js'
import invoiceImportPaymentScheduleForm from '@/composition/invoiceImportPaymentScheduleForm.js'
import RadioGroup from '@/components/forms/RadioGroup.vue'
import RadioWrapper from '@/components/forms/RadioWrapper.vue'
import Radio from '@/components/forms/Radio.vue'
import CheckboxText from '@/components/forms/CheckboxText.vue'
import DatePicker from '@/components/forms/DatePicker.vue'
import Mocker from '@/components/Mocker.vue'

export default defineComponent({
  mixins: [InvoiceMappingFormCollection],
  components: {
    Mocker,
    CheckboxText,
    Radio,
    RadioGroup,
    RadioWrapper,
    Error,
    DatePicker,
  },

  setup (props, context) {
    const { localValue } = hasModelValue(props, context.emit)
    const {
      addPaymentTerm,
      generateSchedule,
      generateTerm,
    } = invoiceImportPaymentScheduleForm()
    const addPaymentSchedule = () => {
      const schedule = generateSchedule()
      let terms = 2 + localValue.value.length

      for (let i = 0; i < terms; i++) {
        schedule.terms.push(generateTerm())
      }

      localValue.value.push(schedule)
    }
    const removePaymentTerm = (schedule, index) => {
      schedule.terms.splice(index, 1)

      if (schedule.terms.length < 2) {
        const scheduleIndex = localValue.value.findIndex(s => s.id === schedule.id)
        localValue.value.splice(scheduleIndex, 1)
      }
    }

    return {
      localValue,
      removePaymentTerm,
      addPaymentTerm,
      addPaymentSchedule,
    }
  }
})
</script>
