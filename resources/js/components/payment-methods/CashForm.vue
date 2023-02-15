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
import InputWrap from '@/components/forms/InputWrap.vue'
import Label from '@/components/forms/Label.vue'
import Wysiwyg from '@/components/forms/Wysiwyg.vue'
import HelpText from '@/components/HelpText.vue'

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
