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
    </div>
  </Modal>
</template>

<script>
import { defineComponent, reactive, ref } from 'vue'
import Modal from '../Modal'
import InputWrap from '../forms/InputWrap'
import Label from '../forms/Label'
import Select from '../forms/Select'
import Checkbox from '../forms/Checkbox'
import CheckboxText from '../forms/CheckboxText'

export default defineComponent({
  emits: ['close', 'apply'],
  components: {
    CheckboxText,
    Checkbox,
    Select,
    Label,
    InputWrap,
    Modal
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
