<template>
  <Authenticated>
    <CardWrapper>
      <form @submit.prevent="save">
        <CardPadding>
          <Fieldset>
            <InputWrap :error="form.errors.files">
              <FileUpload v-model="form.files" :extensions="extensions" />
            </InputWrap>

            <InputWrap :error="form.errors.heading_row">
              <Label for="heading_row" :required="true">{{ __('Heading row') }}</Label>
              <Input v-model="form.heading_row" id="heading_row" type="number" />
              <HelpText>{{ __('A heading row is the row that labels the columns of data. Enter the row number at which the headings are located, which is typically row 1 (the first row).') }}</HelpText>
            </InputWrap>

            <InputWrap :error="form.errors.starting_row">
              <Label for="starting_row" :required="true">{{ __('Data starting row') }}</Label>
              <Input v-model="form.starting_row" id="starting_row" type="number" />
              <HelpText>{{ __('Enter the row number at which you wish to start importing, which is typically row 2 (the row after the header row).') }}</HelpText>
            </InputWrap>
          </Fieldset>
        </CardPadding>

        <CardAction>
          <Button v-if="invoiceImport.mapping_valid" type="submit" :loading="form.processing">
            {{ __('Save') }}
          </Button>
          <Button v-else type="submit" :loading="form.processing">
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
    invoiceImport: {
      type: Object,
      default: () => ({}),
    },
  },

  setup (props) {
    const $route = inject('$route')
    const form = useForm({
      files: props.invoiceImport.files || null,
      heading_row: props.invoiceImport.heading_row || 1,
      starting_row: props.invoiceImport.starting_row || 2,
      _method: props.invoiceImport.id ? 'put' : 'post',
    })
    const save = () => {
      const route = props.invoiceImport.id
        ? $route('invoices.imports.update', props.invoiceImport)
        : $route('invoices.imports.store')

      form.post(route)
    }

    return {
      form,
      save,
    }
  }
})
</script>
