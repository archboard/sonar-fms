<template>
  <Modal
    @close="$emit('close')"
    :headline="__('Family')"
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
            <CheckboxText>{{ __('Create a new family' )}}</CheckboxText>
          </RadioWrapper>

          <FadeIn>
            <div v-if="familyForm.family_id === null" class="mt-4 space-y-4">
              <InputWrap v-if="familyForm.family_id === null" :error="familyForm.errors.name">
                <Label for="name" required>{{ __('Name') }}</Label>
                <Input v-model="familyForm.name" id="name" />
                <HelpText>
                  {{ __("This can just be a name to describe the family, not necessarily the family's surname.") }}
                </HelpText>
              </InputWrap>

              <InputWrap v-if="familyForm.family_id === null" :error="familyForm.errors.notes">
                <Label for="notes">{{ __('Notes') }}</Label>
                <Textarea v-model="familyForm.notes" />
                <HelpText>
                  {{ __("These are internal notes.") }}
                </HelpText>
              </InputWrap>
            </div>
          </FadeIn>
        </div>
      </RadioGroup>

      <BorderSeparator class="my-6" background="bg-white dark:bg-gray-600">
        {{ __('Or find another family') }}
      </BorderSeparator>

      <InputWrap>
        <Label for="family_search">{{ __('Search for family') }}</Label>
        <FamilyTypeahead v-model="existingFamily" id="family_search" />
      </InputWrap>
    </div>
  </Modal>
</template>

<script>
import { defineComponent, ref, watch } from 'vue'
import Modal from '@/components/Modal.vue'
import managesFamilies from '@/composition/managesFamilies.js'
import RadioGroup from '@/components/forms/RadioGroup.vue'
import RadioWrapper from '@/components/forms/RadioWrapper.vue'
import Radio from '@/components/forms/Radio.vue'
import CheckboxText from '@/components/forms/CheckboxText.vue'
import HelpText from '@/components/HelpText.vue'
import InputWrap from '@/components/forms/InputWrap.vue'
import Label from '@/components/forms/Label.vue'
import Input from '@/components/forms/Input.vue'
import Textarea from '@/components/forms/Textarea.vue'
import FadeIn from '@/components/transitions/FadeIn.vue'
import BorderSeparator from '@/components/BorderSeparator.vue'
import FamilyTypeahead from '@/components/forms/FamilyTypeahead.vue'

export default defineComponent({
  components: {
    FamilyTypeahead,
    BorderSeparator,
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
    const existingFamily = ref({})
    const save = (close) => {
      saveStudentsFamily(close)
    }
    watch(existingFamily, value => {
      familyForm.family_id = value.id
    })

    fetchFamilies(familyForm.students)
      .then(data => {
        families.value = data
      })

    return {
      familyForm,
      families,
      save,
      existingFamily,
    }
  }
})
</script>
