<template>
  <Modal
    @close="$emit('close')"
    @action="submitForm"
    :headline="headline"
    :auto-close="false"
    :action-loading="form.processing"
    ref="modal"
  >
    <Fieldset>
      <InputWrap :error="form.errors.name">
        <Label for="name" :required="true">{{ __('Name') }}</Label>
        <Input v-model="form.name" id="name" autofocus />
        <HelpText>{{ __('This is the name will appear in the application and on invoices.') }}</HelpText>
      </InputWrap>
      <InputWrap :error="form.errors.description">
        <Label for="description">{{ __('Description') }}</Label>
        <Textarea v-model="form.description" id="description" />
        <HelpText>{{ __('This describes the scholarship that will be displayed on the invoice.') }}</HelpText>
      </InputWrap>

      <p class="mb-4">
        {{ __('A scholarship can be a set amount and/or percentage that reduces the amount due for an invoice. You may set either a static amount or percentage to be applied to an invoice. If you provide both, you will need to choose a resolution strategy. A resolution strategy is what determines whether to apply the amount or discount percentage.') }}
      </p>

      <InputWrap :error="form.errors.amount">
        <Label for="amount">{{ __('Default amount') }}</Label>
        <Input v-model="form.amount" id="amount" type="number" />
        <HelpText v-html="__('This is a static default scholarship amount that will be deducted from the invoice total amount. The value should be in the smallest units possible for your currency, such as cents. This amount will be displayed as <strong>:amount</strong>', { amount: displayCurrency(form.amount) })" />
      </InputWrap>
      <InputWrap :error="form.errors.percentage">
        <Label for="percentage">{{ __('Default percentage') }}</Label>
        <Input v-model="form.percentage" id="percentage" />
        <HelpText>
          {{ __('This is the default scholarship percentage that will be applied to the invoice. This value is the percentage of the total invoice amount that has been deducted from the invoice. [invoice total] - ([invoice total] * [scholarship percentage]) = [total with scholarship applied].') }}
        </HelpText>
      </InputWrap>

      <InputWrap v-if="form.percentage && form.amount" :error="form.errors.resolution_strategy">
        <Label for="resolution_strategy">{{ __('Resolution strategy') }}</Label>
        <Select v-model="form.resolution_strategy" id="resolution_strategy">
          <option
            v-for="(label, strategy) in strategies"
            :key="strategy"
            :value="strategy"
          >
            {{ label }}
          </option>
        </Select>
        <HelpText>
          {{ __('This resolves whether to use the percentage or amount for the scholarship when both are provided. Least will use whichever has the least amount of discount. Greatest will use whichever has the greatest discount.') }}
        </HelpText>
      </InputWrap>
    </Fieldset>
  </Modal>
</template>

<script>
import { defineComponent, inject, ref, toRef } from 'vue'
import { useForm } from '@inertiajs/inertia-vue3'
import Modal from '../Modal'
import Fieldset from '../forms/Fieldset'
import InputWrap from '../forms/InputWrap'
import Input from '../forms/Input'
import HelpText from '../HelpText'
import Textarea from '../forms/Textarea'
import Label from '../forms/Label'
import displaysCurrency from '../../composition/displaysCurrency'
import Select from '../forms/Select'
import Link from '../Link'
import DepartmentsModal from './DepartmentsModal'
import FeeCategoriesModal from './FeeCategoriesModal'

export default defineComponent({
  components: {
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
    scholarship: {
      type: Object,
      default: () => ({})
    },
    strategies: Object,
  },

  setup (props) {
    const $route = inject('$route')
    const $translate = inject('$translate')
    const modal = ref(null)
    const showDeptModal = ref(false)
    const showCatModal = ref(false)
    const headline = props.scholarship.id
      ? $translate('Update scholarship')
      : $translate('Create a new scholarship')
    const form = useForm({
      name: props.scholarship.name,
      description: props.scholarship.description,
      amount: props.scholarship.amount,
      percentage: props.scholarship.percentage,
      resolution_strategy: props.scholarship.resolution_strategy || 'App\\ResolutionStrategies\\Least',
    })
    const submitForm = () => {
      const route = props.scholarship.id
        ? $route('scholarships.update', props.scholarship.id)
        : $route('scholarships.store')
      const method = props.scholarship.id
        ? 'put'
        : 'post'

      form[method](route, {
        preserveScroll: true,
        onSuccess () {
          modal.value.close()
        }
      })
    }
    const { displayCurrency } = displaysCurrency(toRef(form, 'amount'))

    return {
      form,
      headline,
      submitForm,
      modal,
      displayCurrency,
      showDeptModal,
      showCatModal,
    }
  }
})
</script>
