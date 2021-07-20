<template>
  <Authenticated>
    <template v-slot:actions>
      <Button @click.prevent="saveAndPreview">
        {{ __('Save and preview') }}
      </Button>
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

    <Alert v-if="form.errors.layout_data" level="error" class="mb-4">
      {{ form.errors.layout_data }}
    </Alert>
    <div class="overflow-x-scroll">
      <div class="mx-auto" :style="pageWidth">
        <LayoutBuilder v-model="form.layout_data" />
      </div>
    </div>
  </Authenticated>
</template>

<script>
import { defineComponent, inject, computed } from 'vue'
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
import Alert from '@/components/Alert'

export default defineComponent({
  mixins: [PageProps],
  components: {
    Alert,
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
  props: {
    layout: {
      type: Object,
      default: () => ({})
    }
  },

  setup (props) {
    const $route = inject('$route')
    const form = useForm({
      name: props.layout.name || '',
      paper_size: props.layout.paper_size || 'A4',
      layout_data: props.layout.layout_data || {},
      preview: false,
    })
    const save = () => {
      const method = props.layout.id
        ? 'put'
        : 'post'
      const route = props.layout.id
        ? $route('layouts.update', props.layout.id)
        : $route('layouts.store')

      form[method](route, {
        onFinish: () => {
          if (form.preview) {
            window.open($route('layouts.preview', props.layout), '_blank')
          }

          form.processing = false
          form.preview = false
        }
      })
    }
    const saveAndPreview = () => {
      form.preview = true
      save()
    }
    const pageWidth = computed(() => {
      const widths = {
        A4: `${8.27 * 1.333333}in`,
        Letter: `${8.5 * 1.33333}in`,
      }

      return {
        width: widths[form.paper_size]
      }
    })

    return {
      form,
      save,
      saveAndPreview,
      pageWidth,
    }
  }
})
</script>
