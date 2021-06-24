<template>
  <Authenticated>
    <CardWrapper>
      <form @submit.prevent="form.post($route('invoices.imports.store'))">
        <CardPadding>
          <Fieldset>
            <InputWrap :error="form.errors.files">
              <FileUpload v-model="form.files" :extensions="extensions" />
            </InputWrap>

            <InputWrap :error="form.errors.heading_row">
              <Label for="heading_row" :required="true">{{ __('Heading row') }}</Label>
              <Input v-model="form.heading_row" id="heading_row" type="number" />
              <HelpText>{{ __('A heading row is the row that labels the columns of data. Enter the row number in which the headings are located, which is typically row 1 (the first row).') }}</HelpText>
            </InputWrap>
          </Fieldset>
        </CardPadding>

        <CardAction>
          <Button type="submit" :loading="form.processing">
            {{ __('Start mapping') }}
          </Button>
        </CardAction>
      </form>
    </CardWrapper>
  </Authenticated>
</template>

<script>
import { defineComponent, inject } from 'vue'
import Authenticated from '@/layouts/Authenticated'
import CardWrapper from '@/components/CardWrapper'
import CardPadding from '@/components/CardPadding'
import CardAction from '@/components/CardAction'
import InputWrap from '@/components/forms/InputWrap'
import Label from '@/components/forms/Label'
import Input from '@/components/forms/Input'
import { useForm } from '@inertiajs/inertia-vue3'
import Button from '@/components/Button'
import FileUpload from '@/components/forms/FileUpload'
import Fieldset from '@/components/forms/Fieldset'
import HelpText from '@/components/HelpText'

export default defineComponent({
  components: {
    HelpText,
    Fieldset,
    FileUpload,
    Button,
    Input,
    InputWrap,
    CardAction,
    CardPadding,
    CardWrapper,
    Authenticated,
    Label,
  },

  props: {
    extensions: Array,
  },

  setup () {
    const $route = inject('$route')
    const form = useForm({
      files: null,
      heading_row: 1,
    })
    const save = () => {
      form.post($route('invoices.imports.store'))
    }

    return {
      form,
      save,
    }
  }
})
</script>
