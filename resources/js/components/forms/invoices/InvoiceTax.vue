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
        <PercentInput v-model="localValue.tax_rate" id="tax_rate" />
        <HelpText>{{ __('This is the tax rate percentage to be applied to this invoice.') }}</HelpText>
      </InputWrap>

      <InputWrap v-if="localValue.apply_tax && !localValue.use_school_tax_defaults" :error="localValue.errors.tax_label">
        <Label for="tax_label" :required="true">{{ __('Tax label') }}</Label>
        <Input v-model="localValue.tax_label" id="tax_label" placeholder="VAT" />
        <HelpText>{{ __('This is the label that will be displayed for the name/type of tax.') }}</HelpText>
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
              <CheckboxText>{{ taxItem.name || taxItem.item_id }}</CheckboxText>
            </CheckboxWrapper>

            <FadeIn>
              <div v-if="taxItem.selected" class="pl-6">
                <InputWrap :error="localValue.errors[`tax_items.${index}.tax_rate`]">
                  <Label :for="`tax_items.${index}.tax_rate`" :required="true">{{ __('Tax rate') }}</Label>
                  <PercentInput v-model="taxItem.tax_rate" :id="`tax_items.${index}.tax_rate`" class="w-auto" />
                  <HelpText>{{ __('This is the tax rate percentage to be applied to this item.') }}</HelpText>
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
import { computed, defineComponent, watchEffect } from 'vue'
import hasModelValue from '@/composition/hasModelValue.js'
import CardSectionHeader from '@/components/CardSectionHeader.vue'
import HelpText from '@/components/HelpText.vue'
import InputWrap from '@/components/forms/InputWrap.vue'
import CheckboxWrapper from '@/components/forms/CheckboxWrapper.vue'
import Checkbox from '@/components/forms/Checkbox.vue'
import Input from '@/components/forms/Input.vue'
import Label from '@/components/forms/Label.vue'
import Error from '@/components/forms/Error.vue'
import Fieldset from '@/components/forms/Fieldset.vue'
import CheckboxText from '@/components/forms/CheckboxText.vue'
import FadeIn from '@/components/transitions/FadeIn.vue'
import FadeInGroup from '@/components/transitions/FadeInGroup.vue'
import useSchool from '@/composition/useSchool.js'
import PercentInput from '@/components/forms/PercentInput.vue'
import generatesTaxItems from '@/composition/generatesTaxItems.js'

export default defineComponent({
  components: {
    PercentInput,
    FadeInGroup,
    FadeIn,
    CheckboxText,
    Checkbox,
    CheckboxWrapper,
    InputWrap,
    HelpText,
    CardSectionHeader,
    Input,
    Label,
    Fieldset,
    Error,
  },
  props: {
    modelValue: Object,
  },
  emits: ['update:modelValue'],

  setup (props, { emit }) {
    const { localValue } = hasModelValue(props, emit)
    const { school } = useSchool()
    generatesTaxItems(localValue)

    return {
      localValue,
      school,
    }
  }
})
</script>
