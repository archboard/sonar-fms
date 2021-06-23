<template>
  <Authenticated>
    <CardWrapper>
      <form @submit.prevent="save">
        <CardPadding>
          <InputWrap>
            <FileUpload v-model="form.files" :extensions="extensions" />
          </InputWrap>
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

export default defineComponent({
  components: {
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
    })
    const save = () => {
      form.post($route('invoices.imports.store'), {
        onFinish () {
          // form.processing = false
          form.reset()
        }
      })
    }

    return {
      form,
      save,
    }
  }
})
</script>
