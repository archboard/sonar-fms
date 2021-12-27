<template>
  <Modal
    @close="modalClosed"
    @action="applyFilters"
    :action-text="__('Apply filters')"
  >
    <div class="mt-4 space-y-5">
      <InputWrap>
        <Label for="perPage">{{ __('Results per page') }}</Label>
        <Select id="perPage" v-model="localFilters.perPage">
          <option value="10">10</option>
          <option value="25">25</option>
          <option value="50">50</option>
          <option value="100">100</option>
        </Select>
      </InputWrap>

      <InputWrap>
        <Label for="start-amount">{{ __('Minimum amount') }}</Label>
        <CurrencyInput v-model="localFilters.start_amount" id="start-amount" />
        <HelpText>{{ __('Find payments with at least this amount. Leave empty for no minimum amount.') }}</HelpText>
      </InputWrap>

      <InputWrap>
        <Label for="end-amount">{{ __('Maximum amount') }}</Label>
        <CurrencyInput v-model="localFilters.end_amount" id="end-amount" />
        <HelpText>{{ __('Find payments with at most this amount. Leave empty for no maximum amount.') }}</HelpText>
      </InputWrap>

      <div class="grid grid-cols-2 gap-4">
        <InputWrap>
          <Label for="start-date">{{ __('Payment start date') }}</Label>
          <DatePicker v-model="localFilters.start_date" id="start-date" mode="date" />
          <HelpText>{{ __('Find payments made on or after this date.') }}</HelpText>
        </InputWrap>
        <InputWrap>
          <Label for="end-date">{{ __('Payment end date') }}</Label>
          <DatePicker v-model="localFilters.end_date" id="end-date" mode="date" />
          <HelpText>{{ __('Find payments made on or before this date.') }}</HelpText>
        </InputWrap>
      </div>
    </div>
  </Modal>
</template>

<script>
import { defineComponent, reactive } from 'vue'
import Modal from '@/components/Modal'
import InputWrap from '@/components/forms/InputWrap'
import Label from '@/components/forms/Label'
import Select from '@/components/forms/Select'
import Checkbox from '@/components/forms/Checkbox'
import CheckboxText from '@/components/forms/CheckboxText'
import CheckboxWrapper from '@/components/forms/CheckboxWrapper'
import CurrencyInput from '@/components/forms/CurrencyInput'
import DatePicker from '@/components/forms/DatePicker'
import HelpText from '@/components/HelpText'

export default defineComponent({
  emits: ['close', 'apply'],
  components: {
    HelpText,
    CurrencyInput,
    CheckboxWrapper,
    CheckboxText,
    Checkbox,
    Select,
    Label,
    InputWrap,
    Modal,
    DatePicker,
  },

  props: {
    filters: Object,
    school: Object,
  },

  setup (props, { emit }) {
    const modalClosed = () => {
      emit('close')
    }
    const applyFilters = () => {
      localFilters.page = 1
      emit('apply', localFilters)
    }
    const localFilters = reactive(Object.assign({}, props.filters))

    return {
      modalClosed,
      localFilters,
      applyFilters,
    }
  }
})
</script>
