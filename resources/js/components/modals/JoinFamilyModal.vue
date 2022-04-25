<template>
  <Modal
    @close="$emit('close')"
    :headline="__('Join to family')"
    @action="save"
    :action-loading="familyForm.processing"
    :auto-close="false"
  >
    <div class="space-y-6">
      <p class="text-sm">{{ __('You can either add all students into an existing family, or create a new family.') }}</p>
      <RadioGroup>
        <div v-for="family in families" :key="family.id">
          <RadioWrapper>
            <Radio v-model:checked="familyForm.family_id" :value="family.id" />
            <CheckboxText>{{ family.name }}</CheckboxText>
          </RadioWrapper>
        </div>
        <div>
          <RadioWrapper>
            <Radio v-model:checked="familyForm.family_id" :value="null" />
            <CheckboxText>{{ __('Create new family' )}}</CheckboxText>
          </RadioWrapper>
        </div>
      </RadioGroup>

      <FadeIn>
        <InputWrap v-if="familyForm.family_id === null" :error="familyForm.errors.name">
          <Label for="name" required>{{ __('Name') }}</Label>
          <Input v-model="familyForm.name" id="name" />
          <HelpText>
            {{ __("This can just be a name to describe the family, not necessarily the family's surname.") }}
          </HelpText>
        </InputWrap>
      </FadeIn>

      <FadeIn>
        <InputWrap v-if="familyForm.family_id === null" :error="familyForm.errors.notes">
          <Label for="notes">{{ __('Notes') }}</Label>
          <Textarea v-model="familyForm.notes" />
          <HelpText>
            {{ __("These are internal notes.") }}
          </HelpText>
        </InputWrap>
      </FadeIn>
    </div>
  </Modal>
</template>

<script>
import { defineComponent, ref } from 'vue'
import Modal from '@/components/Modal'
import managesFamilies from '@/composition/managesFamilies'
import RadioGroup from '@/components/forms/RadioGroup'
import RadioWrapper from '@/components/forms/RadioWrapper'
import Radio from '@/components/forms/Radio'
import CheckboxText from '@/components/forms/CheckboxText'
import HelpText from '@/components/HelpText'
import InputWrap from '@/components/forms/InputWrap'
import Label from '@/components/forms/Label'
import Input from '@/components/forms/Input'
import Textarea from '@/components/forms/Textarea'
import FadeIn from '@/components/transitions/FadeIn'

export default defineComponent({
  components: {
    FadeIn,
    Textarea,
    Input,
    Label,
    InputWrap,
    HelpText,
    CheckboxText,
    Radio,
    RadioWrapper,
    RadioGroup,
    Modal,
  },
  emits: ['close'],
  props: {
    students: Array,
  },

  setup (props) {
    const { fetchFamilies, familyForm, saveStudentsFamily } = managesFamilies(props.students)
    const families = ref([])
    const save = (close) => {
      saveStudentsFamily(close)
    }

    fetchFamilies(familyForm.students)
      .then(data => {
        families.value = data
      })

    return {
      familyForm,
      families,
      save,
    }
  }
})
</script>
