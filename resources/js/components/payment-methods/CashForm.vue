<template>
  <InputWrap>
    <Label for="cash-description">{{ __('Instructions') }}</Label>
    <Wysiwyg v-model="localValue.instructions" class="wysiwyg" />
    <HelpText>
      {{ __('These instructions will be displayed on invoices and viewing the invoice online.') }}
    </HelpText>
  </InputWrap>
</template>

<script>
import { defineComponent, ref } from 'vue'
import PaymentMethodFormField from '@/mixins/PaymentMethodFormField'
import InputWrap from '@/components/forms/InputWrap'
import Label from '@/components/forms/Label'
import Wysiwyg from '@/components/forms/Wysiwyg'
import HelpText from '@/components/HelpText'

export default defineComponent({
  mixins: [PaymentMethodFormField],
  components: {
    HelpText,
    Wysiwyg,
    Label,
    InputWrap,
  },
})
</script>
