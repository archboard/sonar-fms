<template>
  <Modal
    @close="$emit('close')"
    @action="submitForm"
    :headline="headline"
    :auto-close="false"
    :action-loading="feeForm.processing"
    ref="modal"
  >
    <Fieldset>
      <InputWrap :error="feeForm.errors.name">
        <Label for="name" :required="true">{{ __('Name') }}</Label>
        <Input v-model="feeForm.name" id="name" />
        <HelpText>{{ __('This is the name will appear in the application and on invoices.') }}</HelpText>
      </InputWrap>
      <InputWrap :error="feeForm.errors.code">
        <Label for="code">{{ __('Fee code') }}</Label>
        <Input v-model="feeForm.code" id="code" />
        <HelpText>{{ __('This is used as an abbreviated name for internal reporting purposes.') }}</HelpText>
      </InputWrap>
      <InputWrap :error="feeForm.errors.description">
        <Label for="description">{{ __('Fee description') }}</Label>
        <Textarea v-model="feeForm.description" id="description" />
        <HelpText>{{ __('This is for internal use and reference only.') }}</HelpText>
      </InputWrap>
      <InputWrap :error="feeForm.errors.amount">
        <Label for="amount" :required="true">{{ __('Default amount') }}</Label>
        <CurrencyInput v-model="feeForm.amount" id="amount" />
      </InputWrap>
      <InputWrap :error="feeForm.errors.fee_category_id">
        <Label for="fee_category_id">{{ __('Fee category') }}</Label>
        <Select v-model="feeForm.fee_category_id">
          <option :value="null">{{ __('No category') }}</option>
          <option
            v-for="cat in categories"
            :key="cat.id"
            :value="cat.id"
          >
            {{ cat.name }}
          </option>
        </Select>
        <Link is="button" class="text-xs" @click.prevent="catOpen">{{ __('Manage fee categories') }}</Link>
        <HelpText>
          {{ __("You can assign a category to help organize this fee in reporting internally, but it isn't required.") }}
        </HelpText>
      </InputWrap>
      <InputWrap :error="feeForm.errors.department_id">
        <Label for="department_id">{{ __('Department') }}</Label>
        <Select v-model="feeForm.department_id">
          <option :value="null">{{ __('No department') }}</option>
          <option
            v-for="dept in departments"
            :key="dept.id"
            :value="dept.id"
          >
            {{ dept.name }}
          </option>
        </Select>
        <Link is="button" class="text-xs" @click.prevent="deptOpen">{{ __('Manage departments') }}</Link>
        <HelpText>
          {{ __("You can assign a department to help organize this fee in reporting internally, but it isn't required.") }}
        </HelpText>
      </InputWrap>
    </Fieldset>

    <DepartmentsModal
      v-if="showDeptModal"
      @close="deptClosed"
    />
    <FeeCategoriesModal
      v-if="showCatModal"
      @close="catClosed"
    />
  </Modal>
</template>

<script>
import { defineComponent, inject, ref, toRef } from 'vue'
import { useForm } from '@inertiajs/inertia-vue3'
import Modal from '@/components/Modal.vue'
import Fieldset from '@/components/forms/Fieldset.vue'
import InputWrap from '@/components/forms/InputWrap.vue'
import Input from '@/components/forms/Input.vue'
import HelpText from '@/components/HelpText.vue'
import Textarea from '@/components/forms/Textarea.vue'
import Label from '@/components/forms/Label.vue'
import displaysCurrency from '@/composition/displaysCurrency'
import handlesDepartments from '@/composition/handlesDepartments'
import handlesFeeCategories from '@/composition/handlesFeeCategories'
import Select from '@/components/forms/Select.vue'
import Link from '@/components/Link.vue'
import DepartmentsModal from '@/components/modals/DepartmentsModal.vue'
import FeeCategoriesModal from '@/components/modals/FeeCategoriesModal.vue'
import CurrencyInput from '@/components/forms/CurrencyInput.vue'

export default defineComponent({
  components: {
    CurrencyInput,
    FeeCategoriesModal,
    DepartmentsModal,
    Select,
    Textarea,
    HelpText,
    Input,
    Link,
    InputWrap,
    Fieldset,
    Label,
    Modal
  },
  emits: ['close'],
  props: {
    fee: {
      type: Object,
      default: () => ({})
    }
  },

  setup (props) {
    const $route = inject('$route')
    const $translate = inject('$translate')
    const modal = ref(null)
    const showDeptModal = ref(false)
    const showCatModal = ref(false)
    const headline = props.fee.id
      ? $translate('Update fee')
      : $translate('Create a new fee')
    const feeForm = useForm({
      name: props.fee.name,
      code: props.fee.code,
      description: props.fee.description,
      amount: props.fee.amount,
      fee_category_id: props.fee.fee_category_id || null,
      department_id: props.fee.department_id || null,
    })
    const submitForm = () => {
      const route = props.fee.id
        ? $route('fees.update', props.fee.id)
        : $route('fees.store')
      const method = props.fee.id
        ? 'put'
        : 'post'

      feeForm[method](route, {
        preserveScroll: true,
        onSuccess () {
          modal.value.close()
        }
      })
    }
    const { displayCurrency } = displaysCurrency(toRef(feeForm, 'amount'))
    const { departments, fetchDepartments } = handlesDepartments()
    const deptOpen = () => {
      modal.value.detachListener()
      showDeptModal.value = true
    }
    const deptClosed = () => {
      fetchDepartments()
      showDeptModal.value = false
      modal.value.attachListener()
    }
    const { categories, fetchCategories } = handlesFeeCategories()
    const catOpen = () => {
      modal.value.detachListener()
      showCatModal.value = true
    }
    const catClosed = () => {
      fetchCategories()
      showCatModal.value = false
      modal.value.attachListener()
    }

    return {
      feeForm,
      headline,
      submitForm,
      modal,
      displayCurrency,
      departments,
      showDeptModal,
      deptOpen,
      deptClosed,
      showCatModal,
      categories,
      catOpen,
      catClosed,
    }
  }
})
</script>
