<template>
  <Authenticated>
    <CardWrapper>
      <form @submit.prevent="save">
        <CardPadding>
          <Fieldset>
            <InputWrap :error="form.errors.driver">
              <Label for="driver">{{ __('Payment method') }}</Label>
              <Select v-model="form.driver" id="driver">
                <option :value="null" disabled>{{ __('Select method') }}</option>
                <option
                  v-for="driver in drivers"
                  :key="driver.key"
                  :value="driver.key"
                >
                  {{ driver.label }}
                </option>
              </Select>
              <HelpText v-if="selectedDriver.description">
                {{ selectedDriver.description }}
              </HelpText>
            </InputWrap>

            <InputWrap :error="form.errors.active">
              <CheckboxWrapper>
                <Checkbox v-model:checked="form.active" />
                <CheckboxText>
                  {{ __('This payment method is active') }}
                </CheckboxText>
              </CheckboxWrapper>
              <HelpText>
                {{ __('If you wish to disable this payment method from being an option, unselect this option.') }}
              </HelpText>
            </InputWrap>

            <InputWrap :error="form.errors.show_on_invoice">
              <CheckboxWrapper>
                <Checkbox v-model:checked="form.show_on_invoice" />
                <CheckboxText>
                  {{ __('Show payment method details on invoice') }}
                </CheckboxText>
              </CheckboxWrapper>
              <HelpText>
                {{ __('If this option is enabled, it will appear on invoices as a possible payment method with accompanying details.') }}
              </HelpText>
            </InputWrap>

            <FadeIn>
              <InputWrap v-if="form.show_on_invoice" :error="form.errors.invoice_description">
                <Label for="invoice_description">{{ __('Invoice description') }}</Label>
                <Textarea v-model="form.invoice_description" id="invoice_description" />
                <HelpText>
                  {{ __('This is the optional description that accompanies the payment method on the invoice.') }}
                </HelpText>
              </InputWrap>
            </FadeIn>

            <FadeIn>
              <component
                v-if="selectedDriver.component"
                :is="selectedDriver.component"
                v-model="form.options"
              />
            </FadeIn>
          </Fieldset>
        </CardPadding>
        <CardAction>
          <Button type="submit" :loading="form.processing">
            {{ __('Save') }}
          </Button>
          <Button component="inertia-link" as="button" :href="$route('payment-methods.index')" color="white">
            {{ __('Cancel') }}
          </Button>
        </CardAction>
      </form>
    </CardWrapper>
  </Authenticated>
</template>

<script>
import { computed, defineComponent, inject, ref } from 'vue'
import PageProps from '@/mixins/PageProps'
import Authenticated from '@/layouts/Authenticated'
import CardWrapper from '@/components/CardWrapper'
import CardPadding from '@/components/CardPadding'
import CardAction from '@/components/CardAction'
import Button from '@/components/Button'
import { useForm } from '@inertiajs/inertia-vue3'
import Fieldset from '@/components/forms/Fieldset'
import InputWrap from '@/components/forms/InputWrap'
import Label from '@/components/forms/Label'
import Select from '@/components/forms/Select'
import isUndefined from 'lodash/isUndefined'
import HelpText from '@/components/HelpText'
import CheckboxWrapper from '@/components/forms/CheckboxWrapper'
import Checkbox from '@/components/forms/Checkbox'
import CheckboxText from '@/components/forms/CheckboxText'
import Textarea from '@/components/forms/Textarea'
import FadeIn from '@/components/transitions/FadeIn'
import CashForm from '@/components/payment-methods/CashForm'

export default defineComponent({
  mixins: [PageProps],
  components: {
    FadeIn,
    Textarea,
    CheckboxText,
    Checkbox,
    CheckboxWrapper,
    HelpText,
    Select,
    InputWrap,
    Fieldset,
    Button,
    CardAction,
    CardPadding,
    CardWrapper,
    Authenticated,
    Label,
    Cash: CashForm,
  },
  props: {
    drivers: Object,
    driver: String,
    paymentMethod: {
      type: Object,
      default: () => ({}),
    }
  },

  setup (props) {
    const $route = inject('$route')
    const form = useForm({
      driver: props.paymentMethod.driver || props.driver || null,
      invoice_description: props.paymentMethod.invoice_description || null,
      options: props.paymentMethod.options || {},
      show_on_invoice: isUndefined(props.paymentMethod.show_on_invoice) ? true : props.paymentMethod.show_on_invoice,
      active: isUndefined(props.paymentMethod.active) ? true : props.paymentMethod.active,
    })
    const selectedDriver = computed(
      () => props.drivers[form.driver] || {}
    )
    const save = () => {
      const route = props.paymentMethod.id
        ? $route('payment-methods.update', props.paymentMethod)
        : $route('payment-methods.store')
      const method = props.paymentMethod.id
        ? 'put'
        : 'post'

      form[method](route, {
        onFinish: () => {
          form.processing = false
        }
      })
    }

    return {
      form,
      selectedDriver,
      save,
    }
  }
})
</script>
