<template>
  <div class="mb-6">
    <CardSectionHeader>{{ __('Invoice details') }}</CardSectionHeader>
    <HelpText>
      {{ __('These are the general details about the invoice.') }}
    </HelpText>
  </div>
  <Fieldset>
    <InputWrap :error="localValue.errors.title">
      <Label for="title" :required="true">{{ __('Title') }}</Label>
      <Input v-model="localValue.title" id="title" required autofocus />
      <HelpText>
        {{ __('Give the invoice a meaningful title that is easily recognizable and descriptive.') }}
      </HelpText>
    </InputWrap>

    <InputWrap>
      <Label for="description">{{ __('Description') }}</Label>
      <Textarea v-model="localValue.description" id="description" />
      <HelpText>
        {{ __('This is a description of the invoice that will be displayed with the invoice.') }}
      </HelpText>
    </InputWrap>

    <InputWrap :error="localValue.errors.invoice_date">
      <Label for="invoice_date">{{ __('Invoice date') }}</Label>
      <DatePicker v-model="localValue.invoice_date" id="invoice_date" mode="date" />
      <HelpText>
        {{ __('This is the date that will appear on the invoice as the date the invoice was issued.') }}
      </HelpText>
    </InputWrap>

    <InputWrap :error="localValue.errors.available_at">
      <Label for="available_at">{{ __('Availability') }}</Label>
      <DatePicker v-model="localValue.available_at" id="available_at" />
      <HelpText>
        {{ __("Set a date and time that this invoice is available to the student's guardians or other contacts. Before the configured time, it will only be viewable to admins. This is helpful to use if you want to prepare and preview invoices before actually making them available for the student. The time is based on your current timezone of :timezone. If this timezone is incorrect you can change it in your Personal Settings.", { timezone }) }}
      </HelpText>
    </InputWrap>

    <InputWrap :error="localValue.errors.due_at">
      <Label for="due_at">{{ __('Due date') }}</Label>
      <DatePicker v-model="localValue.due_at" id="due_at" />
      <HelpText>
        {{ __("Set the date and time that this invoice is due, or don't set one to not have a due date. The time is based on your current timezone of :timezone. If this timezone is incorrect you can change it in your Personal Settings.", { timezone }) }}
      </HelpText>
    </InputWrap>

    <InputWrap :error="localValue.errors.term_id">
      <Label for="term_id">{{ __('Term') }}</Label>
      <Select v-model="localValue.term_id" id="term_id">
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
        <Checkbox v-model:checked="localValue.notify" />
        <CheckboxText>{{ __('Queue notification') }}</CheckboxText>
      </CheckboxWrapper>
      <HelpText>
        {{ __("Having this option enabled will automatically queue an email to be sent notifying the appropriate parties of the available invoice. There is a 15-minute delay of sending the notification which allows you to make adjustments, cancel the notification, or delete the invoice all together. If this is not enabled, you may send a notification manually later.") }}
      </HelpText>
    </InputWrap>
  </Fieldset>
</template>

<script>
import { defineComponent } from 'vue'
import hasModelValue from '@/composition/hasModelValue'
import CardSectionHeader from '@/components/CardSectionHeader'
import HelpText from '@/components/HelpText'
import InputWrap from '@/components/forms/InputWrap'
import CheckboxWrapper from '@/components/forms/CheckboxWrapper'
import DatePicker from '@/components/forms/DatePicker'
import Checkbox from '@/components/forms/Checkbox'
import Textarea from '@/components/forms/Textarea'
import Fieldset from '@/components/forms/Fieldset'
import Select from '@/components/forms/Select'
import Input from '@/components/forms/Input'
import Label from '@/components/forms/Label'
import CheckboxText from '@/components/forms/CheckboxText'
import FadeIn from '@/components/transitions/FadeIn'
import FadeInGroup from '@/components/transitions/FadeInGroup'
import fetchesTerms from '@/composition/fetchesTerms'
import displaysDate from '@/composition/displaysDate'

export default defineComponent({
  components: {
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
    DatePicker,
    Textarea,
    Fieldset,
    Select,
  },
  props: {
    modelValue: Object,
  },
  emits: ['update:modelValue'],

  setup (props, { emit }) {
    const { localValue } = hasModelValue(props, emit)
    const { terms } = fetchesTerms()
    const { timezone, displayDate } = displaysDate()

    return {
      localValue,
      terms,
      timezone,
      displayDate,
    }
  }
})
</script>
