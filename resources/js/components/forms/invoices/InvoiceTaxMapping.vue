<template>
  <div class="mb-6">
    <CardSectionHeader>
      {{ __('Taxes') }}
    </CardSectionHeader>
    <HelpText class="text-sm mt-1">
      {{ __('Add tax details for this invoice.') }}
    </HelpText>
  </div>

  <Fieldset>
    <InputWrap :error="localValue.errors.apply_tax">
      <CheckboxWrapper>
        <Checkbox v-model:checked="localValue.apply_tax" />
        <CheckboxText>{{ __('Apply tax rate to this invoice.') }}</CheckboxText>
      </CheckboxWrapper>
      <HelpText>{{ __('When this option is enabled, a tax is added to the amount due.') }}</HelpText>
    </InputWrap>

    <FadeIn>
      <InputWrap v-if="localValue.apply_tax" :error="localValue.errors.use_school_tax_defaults">
        <CheckboxWrapper>
          <Checkbox v-model:checked="localValue.use_school_tax_defaults" />
          <CheckboxText>{{ __('Use school default tax rate and label - :label (:rate).', { label: school.tax_label, rate: school.tax_rate_formatted }) }}</CheckboxText>
        </CheckboxWrapper>
      </InputWrap>
    </FadeIn>

    <FadeInGroup>
      <InputWrap v-if="localValue.apply_tax && !localValue.use_school_tax_defaults" :error="localValue.errors.tax_rate">
        <Label for="tax_rate" :required="true">{{ __('Tax rate') }}</Label>
        <MapField v-model="localValue.tax_rate" :headers="headers" id="tax_rate">
          <Input v-model="localValue.tax_rate.value" id="tax_rate" />
          <template v-slot:after>
            <HelpText>{{ __('This is the tax rate percentage to be applied to this invoice.') }}</HelpText>
          </template>
        </MapField>
      </InputWrap>

      <InputWrap v-if="localValue.apply_tax && !localValue.use_school_tax_defaults" :error="localValue.errors.tax_label">
        <Label for="tax_label" :required="true">{{ __('Tax label') }}</Label>
        <MapField v-model="localValue.tax_label" :headers="headers" id="tax_label">
          <Input v-model="localValue.tax_label.value" id="tax_label" placeholder="VAT" />
          <template v-slot:after>
            <HelpText>{{ __('This is the label that will be displayed for the name/type of tax.') }}</HelpText>
          </template>
        </MapField>
      </InputWrap>
    </FadeInGroup>

    <InputWrap v-if="localValue.tax_items.length > 1" :error="localValue.errors.apply_tax_to_all_items">
      <CheckboxWrapper>
        <Checkbox v-model:checked="localValue.apply_tax_to_all_items" />
        <CheckboxText>{{ __('Apply tax to whole invoice') }}</CheckboxText>
      </CheckboxWrapper>

      <FadeIn>
        <div v-if="!localValue.apply_tax_to_all_items" class="mt-3 ml-8 space-y-1">
          <HelpText>
            {{ __('Apply tax to the following items:') }}
          </HelpText>
          <div
            v-for="(taxItem, index) in localValue.tax_items"
            :key="taxItem.item_id"
          >
            <CheckboxWrapper>
              <Checkbox v-model:checked="taxItem.selected" />
              <CheckboxText>
                <span v-if="taxItem.name.isManual">
                  {{ taxItem.name.value || taxItem.item_id }}
                </span>
                <span v-else class="flex items-center space-x-1">
                  <LinkIcon class="w-4 h-4" />
                  <span>{{ taxItem.name.column }}</span>
                </span>
              </CheckboxText>
            </CheckboxWrapper>

            <FadeIn>
              <div v-if="taxItem.selected" class="pl-6">
                <InputWrap :error="localValue.errors[`tax_items.${index}.tax_rate`]">
                  <Label :for="`tax_items.${index}.tax_rate`" :required="true">{{ __('Tax rate') }}</Label>
                  <MapField v-model="taxItem.tax_rate" :headers="headers" :id="`tax_items.${index}.tax_rate`">
                    <PercentInput v-model="taxItem.tax_rate.value" :id="`tax_items.${index}.tax_rate`" class="w-auto" />
                    <template #after>
                      <HelpText>{{ __('This is the tax rate percentage to be applied to this item.') }}</HelpText>
                    </template>
                  </MapField>
                  <pre>{{ taxItem.tax_rate }}</pre>
                </InputWrap>
              </div>
            </FadeIn>
          </div>
          <Error v-if="localValue.errors.tax_items">
            {{ localValue.errors.tax_items }}
          </Error>
        </div>
      </FadeIn>
    </InputWrap>
  </Fieldset>
</template>

<script>
import { defineComponent } from 'vue'
import InputWrap from '@/components/forms/InputWrap'
import CheckboxWrapper from '@/components/forms/CheckboxWrapper'
import Checkbox from '@/components/forms/Checkbox'
import CheckboxText from '@/components/forms/CheckboxText'
import HelpText from '@/components/HelpText'
import FadeIn from '@/components/transitions/FadeIn'
import FadeInGroup from '@/components/transitions/FadeInGroup'
import MapField from '@/components/forms/MapField'
import Input from '@/components/forms/Input'
import Label from '@/components/forms/Label'
import Fieldset from '@/components/forms/Fieldset'
import PercentInput from '@/components/forms/PercentInput'
import Error from '@/components/forms/Error'
import hasModelValue from '@/composition/hasModelValue'
import CardSectionHeader from '@/components/CardSectionHeader'
import useSchool from '@/composition/useSchool'
import generatesTaxItems from '@/composition/generatesTaxItems'
import { LinkIcon } from '@heroicons/vue/outline'

export default defineComponent({
  components: {
    CardSectionHeader,
    PercentInput,
    MapField,
    FadeInGroup,
    FadeIn,
    HelpText,
    CheckboxText,
    Checkbox,
    CheckboxWrapper,
    InputWrap,
    Input,
    Label,
    Fieldset,
    Error,
    LinkIcon,
  },
  props: {
    modelValue: Object,
    headers: Array,
  },
  emits: ['update:modelValue'],

  setup (props, { emit }) {
    const { localValue } = hasModelValue(props, emit)
    const { school } = useSchool()
    generatesTaxItems(localValue, true)

    return {
      localValue,
      school,
    }
  }
})
</script>
