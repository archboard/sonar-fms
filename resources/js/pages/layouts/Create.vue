<template>
  <Authenticated>
    <template v-slot:actions>
      <Button @click.prevent="save" :loading="form.processing">
        {{ __('Save') }}
      </Button>
    </template>

    <CardWrapper class="mb-8">
      <CardPadding>
        <TwoColumnWrapper>
          <InputWrap :error="form.errors.name">
            <Label for="name" :required="true">{{ __('Name') }}</Label>
            <Input v-model="form.name" />
            <HelpText>
              {{ __('Give the layout a meaningful name so you can identify it later.') }}
            </HelpText>
          </InputWrap>
          <InputWrap :error="form.errors.paper_size">
            <Label for="paper_size">{{ __('Paper size') }}</Label>
            <Select id="paper_size" v-model="form.paper_size">
              <option value="A4">A4</option>
              <option value="Letter">Letter</option>
            </Select>
            <HelpText>
              {{ __('This will be size of the pages that are in the PDF file.') }}
            </HelpText>
          </InputWrap>
        </TwoColumnWrapper>
      </CardPadding>
    </CardWrapper>

    <LayoutBuilder v-model="form.layout_data" />
  </Authenticated>
</template>

<script>
import { defineComponent, inject, ref } from 'vue'
import Authenticated from '@/layouts/Authenticated'
import PageProps from '@/mixins/PageProps'
import Button from '@/components/Button'
import CardWrapper from '@/components/CardWrapper'
import CardPadding from '@/components/CardPadding'
import InputWrap from '@/components/forms/InputWrap'
import Label from '@/components/forms/Label'
import Input from '@/components/forms/Input'
import { useForm } from '@inertiajs/inertia-vue3'
import HelpText from '@/components/HelpText'
import LayoutBuilder from '@/pages/layouts/LayoutBuilder'
import TwoColumnWrapper from '@/components/TwoColumnWrapper'
import Select from '@/components/forms/Select'

export default defineComponent({
  mixins: [PageProps],
  components: {
    Select,
    TwoColumnWrapper,
    LayoutBuilder,
    HelpText,
    Input,
    InputWrap,
    CardPadding,
    CardWrapper,
    Button,
    Authenticated,
    Label,
  },

  setup () {
    const $route = inject('$route')
    const form = useForm({
      name: '',
      paper_size: 'A4',
      layout_data: {},
    })
    const save = () => {
      form.post($route('layouts.store'), {
        onFinish: () => {
          form.processing = false
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
