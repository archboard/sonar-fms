<template>
  <Modal
    @close="$emit('close')"
    :action-text="__('Export')"
    :action-loading="form.processing"
    @action="startExport"
  >
    <Fieldset>
      <InputWrap :error="form.errors.name">
        <Label required>{{ __('File name') }}</Label>
        <Input v-model="form.name" />
        <HelpText>{{ __('The file extension will added automatically.') }}</HelpText>
      </InputWrap>

      <InputWrap :error="form.errors.format">
        <Label required>{{ __('File format') }}</Label>
        <Select v-model="form.format">
          <option value="xlsx">Excel (xlsx)</option>
          <option value="csv">CSV</option>
        </Select>
      </InputWrap>

      <InputWrap :error="form.errors.apply_filters">
        <CheckboxWrapper>
          <Checkbox v-model:checked="form.apply_filters" />
          <CheckboxText>{{ __('Use current filters') }}</CheckboxText>
        </CheckboxWrapper>
        <FadeIn>
          <Alert v-if="!form.apply_filters" level="warning" class="mt-2">{{ __('This will export everything.') }}</Alert>
        </FadeIn>
      </InputWrap>
    </Fieldset>
  </Modal>
</template>

<script>
import { defineComponent, inject, ref } from 'vue'
import Modal from '@/components/Modal.vue'
import { useForm } from '@inertiajs/vue3'
import Fieldset from '@/components/forms/Fieldset.vue'
import InputWrap from '@/components/forms/InputWrap.vue'
import Label from '@/components/forms/Label.vue'
import Input from '@/components/forms/Input.vue'
import displaysDate from '@/composition/displaysDate.js'
import Select from '@/components/forms/Select.vue'
import CheckboxWrapper from '@/components/forms/CheckboxWrapper.vue'
import Checkbox from '@/components/forms/Checkbox.vue'
import CheckboxText from '@/components/forms/CheckboxText.vue'
import HelpText from '@/components/HelpText.vue'
import Alert from '@/components/Alert.vue'
import FadeIn from '@/components/transitions/FadeIn.vue'

export default defineComponent({
  components: {
    FadeIn,
    Alert,
    HelpText,
    CheckboxText,
    Checkbox,
    CheckboxWrapper,
    Select,
    Input,
    Label,
    InputWrap,
    Fieldset,
    Modal,
  },
  emits: ['close'],
  props: {
    url: {
      type: String,
      required: true,
    },
    filters: {
      type: Object,
      default: () => ({})
    },
  },

  setup (props) {
    const { displayDate } = displaysDate()
    const __ = inject('$translate')
    const form = useForm({
      name: __('Export :date', { date: displayDate(new Date, 'short') }),
      format: 'xlsx',
      apply_filters: true,
      filters: props.filters,
    })
    const startExport = () => {
      form.post(props.url)
    }

    return {
      form,
      startExport,
    }
  }
})
</script>
