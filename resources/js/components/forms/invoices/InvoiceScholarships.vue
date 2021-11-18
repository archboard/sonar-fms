<template>
  <div class="mb-6">
    <CardSectionHeader>
      {{ __('Scholarships') }}
    </CardSectionHeader>
    <HelpText class="text-sm mt-1">
      {{ __('Add scholarships to reduce the amount due for the invoice.') }}
    </HelpText>
  </div>

  <ul class="space-y-3">
    <FadeInGroup>
      <li
        v-for="(item, index) in localValue"
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
                    {{ scholarship.name }} {{ scholarship.description ? `- ${scholarship.description}` : '' }}
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
                <PercentInput v-model="item.percentage" :id="`scholarship_percentage_${index}`" />
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
                <Button color="red" size="sm" type="button" @click.prevent="localValue.splice(index, 1)">
                  <TrashIcon class="w-4 h-4" />
                  <span class="ml-2">{{ __('Remove scholarship') }}</span>
                </Button>
              </div>
            </Fieldset>
          </CardPadding>
        </CardWrapper>
      </li>
    </FadeInGroup>
  </ul>

  <AddThingButton @click="addScholarship">
    {{ __('Add scholarship') }}
  </AddThingButton>
</template>

<script>
import { defineComponent } from 'vue'
import invoiceScholarshipForm from '@/composition/invoiceScholarshipForm'
import invoiceFormComponent from '@/composition/invoiceFormComponent'
import InvoiceFormCollection from '@/mixins/InvoiceFormCollection'
import CheckboxWrapper from '@/components/forms/CheckboxWrapper'
import Checkbox from '@/components/forms/Checkbox'
import CheckboxText from '@/components/forms/CheckboxText'
import AddThingButton from '@/components/forms/AddThingButton'
import PercentInput from '@/components/forms/PercentInput'
import fetchesResolutionStrategies from '@/composition/fetchesResolutionStrategies'

export default defineComponent({
  components: {
    PercentInput,
    AddThingButton,
    CheckboxText,
    Checkbox,
    CheckboxWrapper,
  },
  mixins: [InvoiceFormCollection],

  setup (props, context) {
    const { localValue, displayCurrency } = invoiceFormComponent(props, context)
    const {
      scholarships,
      scholarshipSubtotal,
      getItemDiscount,
      addScholarship,
      scholarshipSelected
    } = invoiceScholarshipForm(props.form)
    const { strategies } = fetchesResolutionStrategies()

    return {
      localValue,
      displayCurrency,
      scholarships,
      scholarshipSubtotal,
      getItemDiscount,
      addScholarship,
      scholarshipSelected,
      strategies,
    }
  }
})
</script>
