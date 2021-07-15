<template>
  <Authenticated>
    <template v-slot:actions>
      <Button @click.prevent="save">
        {{ __('Save') }}
      </Button>
    </template>

    <CardWrapper class="mb-8">
      <CardPadding>
        <InputWrap>
          <Label for="name" :required="true">{{ __('Name') }}</Label>
          <Input v-model="form.name" />
          <HelpText>
            {{ __('Give the layout a meaningful name so you can identify it later.') }}
          </HelpText>
        </InputWrap>
      </CardPadding>
    </CardWrapper>

    <LayoutBuilder v-model="form.layout_data" />
  </Authenticated>
</template>

<script>
import { defineComponent, ref } from 'vue'
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

export default defineComponent({
  mixins: [PageProps],
  components: {
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
    const form = useForm({
      name: '',
      paper_size: 'A4',
      layout_data: {},
    })
    const save = () => {}

    return {
      form,
      save,
    }
  }
})
</script>
