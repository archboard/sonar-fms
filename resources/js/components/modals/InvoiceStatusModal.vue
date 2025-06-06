<template>
  <Modal
    @close="$emit('close')"
    :action-loading="form.processing"
    @action="save"
    :auto-close="false"
  >
    <p class="text-sm font-bold">
      {{ __('Mark invoice as...') }}
    </p>
    <Fieldset class="pt-4">
      <InputWrap :error="form.errors.status">
        <RadioGroup>
<!--          <div>-->
<!--            <RadioWrapper>-->
<!--              <Radio v-model:checked="form.status" value="paid_at" />-->
<!--              <CheckboxText>{{ __('Paid' )}}</CheckboxText>-->
<!--            </RadioWrapper>-->
<!--            <HelpText class="pl-6">-->
<!--              {{ __('Payment was collected outside of Sonar FMS.') }}-->
<!--            </HelpText>-->
<!--          </div>-->

          <div>
            <RadioWrapper>
              <Radio v-model:checked="form.status" value="voided_at" />
              <CheckboxText class="font-medium">{{ __('Void' )}}</CheckboxText>
            </RadioWrapper>
            <HelpText class="pl-6">
              {{ __('This invoice was accidentally created or contains a mistake.') }}
            </HelpText>
          </div>
          <div>
            <RadioWrapper>
              <Radio v-model:checked="form.status" value="canceled_at" />
              <CheckboxText class="font-medium">{{ __('Canceled' )}}</CheckboxText>
            </RadioWrapper>
            <HelpText class="pl-6">
              {{ __('This invoice will not be repaid. The remaining balance will be removed.') }}
            </HelpText>
          </div>
        </RadioGroup>
      </InputWrap>

      <FadeIn>
        <InputWrap v-if="form.status === 'voided_at'">
          <CheckboxWrapper>
            <Checkbox v-model:checked="form.duplicate" />
            <CheckboxText>
              {{ __('Start duplicate invoice based on this one.') }}
            </CheckboxText>
          </CheckboxWrapper>
          <HelpText>
            {{ __('After saving the updated status, you will use this invoice as the starting template for a new invoice. This is helpful if the invoice has an error.') }}
          </HelpText>
        </InputWrap>
      </FadeIn>
    </Fieldset>

    <Alert v-if="form.status" level="warning" class="mt-4">
      {{ __('This action cannot be undone.') }}
    </Alert>
  </Modal>
</template>

<script>
import { defineComponent, inject, ref } from 'vue'
import Modal from '@/components/Modal.vue'
import { useForm } from '@inertiajs/vue3'
import Fieldset from '@/components/forms/Fieldset.vue'
import InputWrap from '@/components/forms/InputWrap.vue'
import RadioWrapper from '@/components/forms/RadioWrapper.vue'
import Radio from '@/components/forms/Radio.vue'
import RadioGroup from '@/components/forms/RadioGroup.vue'
import CheckboxText from '@/components/forms/CheckboxText.vue'
import HelpText from '@/components/HelpText.vue'
import Alert from '@/components/Alert.vue'
import CheckboxWrapper from '@/components/forms/CheckboxWrapper.vue'
import Checkbox from '@/components/forms/Checkbox.vue'
import FadeIn from '@/components/transitions/FadeIn.vue'

export default defineComponent({
  components: {
    FadeIn,
    Checkbox,
    CheckboxWrapper,
    Alert,
    HelpText,
    CheckboxText,
    Radio,
    RadioWrapper,
    RadioGroup,
    InputWrap,
    Fieldset,
    Modal,
  },
  emits: ['close'],
  props: {
    invoice: Object,
  },

  setup (props) {
    const $route = inject('$route')
    const form = useForm({
      status: null,
      duplicate: false,
    })
    const save = close => {
      form.post($route('invoices.status', props.invoice), {
        preserveScroll: true,
        onSuccess: () => close(),
      })
    }

    return {
      form,
      save,
    }
  }
})
</script>
