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
import Error from '@/components/forms/Error.vue'
import InvoiceMappingFormCollection from '@/mixins/InvoiceMappingFormCollection'
import hasModelValue from '@/composition/hasModelValue.js'
import invoiceImportScholarshipForm from '@/composition/invoiceImportScholarshipForm.js'
import RadioGroup from '@/components/forms/RadioGroup.vue'
import RadioWrapper from '@/components/forms/RadioWrapper.vue'
import Radio from '@/components/forms/Radio.vue'
import CheckboxText from '@/components/forms/CheckboxText.vue'
import CheckboxWrapper from '@/components/forms/CheckboxWrapper.vue'
import Checkbox from '@/components/forms/Checkbox.vue'
import { LinkIcon } from '@heroicons/vue/outline'

export default defineComponent({
  mixins: [InvoiceMappingFormCollection],
  components: {
    Checkbox,
    CheckboxWrapper,
    CheckboxText,
    Radio,
    RadioGroup,
    RadioWrapper,
    Error,
    LinkIcon,
  },

  setup (props, context) {
    const { localValue } = hasModelValue(props, context.emit)
    const {
      scholarships,
      makeScholarship,
      scholarshipSelected
    } = invoiceImportScholarshipForm(props.form)
    const addScholarship = () => {
      localValue.value.push(makeScholarship())
    }

    return {
      localValue,
      scholarships,
      addScholarship,
      scholarshipSelected,
    }
  }
})
</script>
