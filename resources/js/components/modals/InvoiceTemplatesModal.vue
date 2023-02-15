<template>
  <Modal
    @action="save"
    @close="$emit('close')"
    :headline="__('Templates')"
    :action-loading="templateForm.processing"
    :initial-focus="input"
    ref="modal"
  >
    <ul class="space-y-2">
      <li
        v-for="template in templates"
        :key="template.id"
        class="flex items-center justify-between text-sm"
      >
        <span>
          {{ template.name }} <span v-if="template.user" class="text-gray-500 dark:text-gray-400">{{ __('Created by :name', { name: template.user.full_name }) }}</span>
        </span>

        <div class="flex space-x-2">
          <Link is="button" class="text-sm" @click.prevent="useTemplate(template)">
            {{ __('Use') }}
          </Link>
          <Link is="button" class="text-sm" @click.prevent="editTemplate(template)">
            {{ __('Edit') }}
          </Link>
          <Link is="button" class="text-sm" @click.prevent="promptDelete(template)">
            {{ __('Delete') }}
          </Link>
        </div>
      </li>
    </ul>

    <form @submit.prevent="save">
      <ModalHeadline v-if="templateForm.id" class="mb-4 mt-6">{{ __('Update template') }}</ModalHeadline>
      <ModalHeadline v-else class="mb-4 mt-6">{{ __('Save as new template') }}</ModalHeadline>
      <Fieldset>
        <InputWrap :error="templateForm.errors.name">
          <Label for="new-template-name" :required="true">{{ __('Name') }}</Label>
          <Input v-model="templateForm.name" :placeholder="__('My template name')" id="new-template-name" ref="input" />
        </InputWrap>
      </Fieldset>
    </form>
  </Modal>
</template>

<script>
import { defineComponent, ref } from 'vue'
import Modal from '@/components/Modal.vue'
import { useForm } from '@inertiajs/vue3'
import handlesInvoiceTemplates from '@/composition/handlesInvoiceTemplates.js'
import Fieldset from '@/components/forms/Fieldset.vue'
import Label from '@/components/forms/Label.vue'
import InputWrap from '@/components/forms/InputWrap.vue'
import Input from '@/components/forms/Input.vue'
import ModalHeadline from '@/components/modals/ModalHeadline.vue'
import Link from '@/components/Link.vue'

export default defineComponent({
  components: {
    ModalHeadline,
    Input,
    InputWrap,
    Fieldset,
    Modal,
    Label,
    Link,
  },

  props: {
    invoice: Object,
    forImport: {
      type: Boolean,
      default: false
    },
    routeBase: {
      type: String,
      default: '/templates',
    }
  },
  emits: ['use', 'close'],

  setup (props, { emit }) {
    const modal = ref()
    const input = ref()
    const templateForm = useForm({
      id: null,
      name: '',
      template: {},
      for_import: props.forImport,
    })
    const { templates, saveTemplate, deleteTemplate } = handlesInvoiceTemplates(props.forImport, props.routeBase)
    const editTemplate = template => {
      templateForm.id = template.id
      templateForm.name = template.name
      templateForm.template = template.template
    }
    const save = async () => {
      // If a form object was passed
      templateForm.template = typeof props.invoice.data === 'function'
        ? props.invoice.data()
        : props.invoice

      templateForm.processing = true
      await saveTemplate(templateForm)
      templateForm.processing = false
      templateForm.reset()
    }
    const useTemplate = template => {
      emit('use', template)
      modal.value.close()
    }
    const promptDelete = template => {
      deleteTemplate(template)
    }

    return {
      templates,
      templateForm,
      save,
      editTemplate,
      useTemplate,
      promptDelete,
      deleteTemplate,
      modal,
      input,
    }
  }
})
</script>
