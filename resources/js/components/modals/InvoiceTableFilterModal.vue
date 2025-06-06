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

      <div class="grid grid-cols-2 gap-4">
        <InputWrap>
          <Label for="date_start">{{ __('Invoice date start') }}</Label>
          <DatePicker
            v-model="localFilters.date_start"
            id="date_start"
            mode="date"
          />
        </InputWrap>
        <InputWrap>
          <Label for="date_end">{{ __('Invoice date end') }}</Label>
          <DatePicker
            v-model="localFilters.date_end"
            id="date_end"
            mode="date"
          />
        </InputWrap>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <InputWrap>
          <Label for="due_start">{{ __('Due date start') }}</Label>
          <DatePicker
            v-model="localFilters.due_start"
            id="due_start"
            mode="date"
          />
        </InputWrap>
        <InputWrap>
          <Label for="due_end">{{ __('Due date end') }}</Label>
          <DatePicker
            v-model="localFilters.due_end"
            id="due_end"
            mode="date"
          />
        </InputWrap>
      </div>

      <InputWrap>
        <Label>{{ __('Status') }}</Label>
        <div class="grid grid-cols-3 gap-2">
          <CheckboxWrapper
            v-for="(label, key) in statuses"
            :key="key"
          >
            <Checkbox v-model:checked="localFilters.status" :value="key" />
            <CheckboxText>{{ label }}</CheckboxText>
          </CheckboxWrapper>
        </div>
      </InputWrap>

      <InputWrap>
        <Label>{{ __('Grade levels') }}</Label>
        <div class="grid grid-cols-6 gap-2">
          <CheckboxWrapper v-for="grade in school.grade_levels" :key="grade">
            <Checkbox v-model:checked="localFilters.grades" :value="grade" />
            <CheckboxText>{{ displayShortGrade(grade) }}</CheckboxText>
          </CheckboxWrapper>
        </div>
      </InputWrap>

      <InputWrap>
        <Label>{{ __('Type') }}</Label>
        <div class="grid grid-cols-3 gap-2">
          <CheckboxWrapper v-for="(label, key) in types" :key="key">
            <Checkbox v-model:checked="localFilters.types" :value="key" />
            <CheckboxText>{{ label }}</CheckboxText>
          </CheckboxWrapper>
        </div>
      </InputWrap>
    </div>
  </Modal>
</template>

<script>
import { defineComponent, reactive } from 'vue'
import invoiceTypes from '@/composition/invoiceTypes.js'
import Modal from '@/components/Modal.vue'
import InputWrap from '@/components/forms/InputWrap.vue'
import Label from '@/components/forms/Label.vue'
import Select from '@/components/forms/Select.vue'
import Checkbox from '@/components/forms/Checkbox.vue'
import CheckboxText from '@/components/forms/CheckboxText.vue'
import CheckboxWrapper from '@/components/forms/CheckboxWrapper.vue'
import DatePicker from '@/components/forms/DatePicker.vue'
import displaysGrades from '@/composition/displaysGrades.js'
import invoiceStatuses from '@/composition/invoiceStatuses.js'
import useSchool from '@/composition/useSchool.js'

export default defineComponent({
  emits: ['close', 'apply'],
  components: {
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
    blockedStatuses: {
      type: Array,
      default: () => []
    },
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
    const { displayShortGrade } = displaysGrades()
    const { statuses } = invoiceStatuses(props.blockedStatuses)
    const { school } = useSchool()
    const types = invoiceTypes()

    return {
      modalClosed,
      localFilters,
      applyFilters,
      displayShortGrade,
      statuses,
      school,
      types,
    }
  }
})
</script>
