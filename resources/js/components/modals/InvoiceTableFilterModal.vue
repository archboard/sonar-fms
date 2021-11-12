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
          <CheckboxWrapper v-for="grade in school.grade_levels">
            <Checkbox v-model:checked="localFilters.grades" :value="grade" />
            <CheckboxText>{{ displayShortGrade(grade) }}</CheckboxText>
          </CheckboxWrapper>
        </div>
      </InputWrap>
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
import displaysGrades from '@/composition/displaysGrades'
import invoiceStatuses from '@/composition/invoiceStatuses'
import useSchool from '@/composition/useSchool'

export default defineComponent({
  emits: ['close', 'apply'],
  components: {
    CheckboxWrapper,
    CheckboxText,
    Checkbox,
    Select,
    Label,
    InputWrap,
    Modal
  },

  props: {
    filters: Object,
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
    const { statuses } = invoiceStatuses()
    const { school } = useSchool()

    return {
      modalClosed,
      localFilters,
      applyFilters,
      displayShortGrade,
      statuses,
      school,
    }
  }
})
</script>
