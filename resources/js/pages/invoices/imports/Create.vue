<template>
  <Authenticated>
    <CardWrapper>
      <form @submit.prevent="save">
        <CardPadding>
          <Alert class="mb-4">
            {{ __('Please ensure the file is not password protected before uploading.') }}
          </Alert>

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
          <Button v-if="existingImport.mapping_valid" type="submit" :loading="form.processing">
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
import Alert from '@/components/Alert'

export default defineComponent({
  components: {
    Alert,
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
    existingImport: {
      type: Object,
      default: () => ({}),
    },
    endpoint: String,
    method: String,
  },

  setup (props) {
    const form = useForm({
      files: props.existingImport.files || null,
      heading_row: props.existingImport.heading_row || 1,
      starting_row: props.existingImport.starting_row || 2,
      _method: props.method,
    })
    const save = () => {
      form.post(props.endpoint)
    }

    return {
      form,
      save,
    }
  }
})
</script>
