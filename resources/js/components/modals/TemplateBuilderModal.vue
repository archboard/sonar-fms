<template>
  <Modal
    @close="$emit('close')"
    @action="$emit('useTemplate', localContent)"
    :headline="__('Build a template')"
    :action-text="__('Use template')"
  >
    <HelpText class="mb-3">
      {{ __("Templates are a powerful way to generate dynamic content using a handful of available placeholders. You can build and preview your template below. When you are happy with what you've built, click on the Use button.") }}
    </HelpText>
    <HelpText class="mb-2">{{ __('Available placeholders:') }}</HelpText>
    <ul class="list-disc pl-5 mb-3 text-sm text-gray-500 dark:text-gray-300 space-y-1">
      <li v-for="(description, placeholder) in placeholders" :key="placeholder">
        <div class="flex items-start justify-between">
          <div><CodeText>{{ placeholder }}</CodeText> - {{ description }}</div>
          <button type="button" @click.prevent="localContent += placeholder">
            <PlusIcon class="w-4 h-4 text-gray-500 dark:text-gray-400" />
          </button>
        </div>
      </li>
    </ul>
    <Input v-model="localContent" @keydown="keyListener" />
    <FadeIn>
      <div v-if="preview" class="mt-2">
        <h3 class="font-medium mb-2">{{ __('Preview sample') }}</h3>
        <div class="py-2 px-3 rounded-md border-gray-200 dark:border-gray-400 bg-gray-300 dark:bg-gray-800">
          {{ preview }}
        </div>
      </div>
    </FadeIn>
  </Modal>
</template>

<script>
import { defineComponent, inject, ref, watch } from 'vue'
import Modal from '@/components/Modal.vue'
import Input from '@/components/forms/Input.vue'
import HelpText from '@/components/HelpText.vue'
import CodeText from '@/components/CodeText.vue'
import { PlusIcon } from '@heroicons/vue/outline'
import FadeIn from '@/components/transitions/FadeIn.vue'
import debounce from 'lodash/debounce'

export default defineComponent({
  components: {
    FadeIn,
    CodeText,
    HelpText,
    Input,
    Modal,
    PlusIcon,
  },
  emits: ['close', 'useTemplate'],
  props: {
    content: {
      type: String,
      default: ''
    }
  },

  setup (props) {
    const localContent = ref(props.content)
    const preview = ref(props.content)
    const __ = inject('$translate')
    const $http = inject('$http')
    const $error = inject('$error')
    const placeholders = {
      '{year}': __('The current year.'),
      '{month}': __('The current month.'),
      '{day}': __('The current day.'),
      '{term}': __('The current school term (e.g. S1).'),
      '{school_year}': __('The current school year (e.g. 20-21).'),
      '{next_school_year}': __('The next school year (e.g. 20-21).'),
      '{student_number}': __("The student number of the invoice's student."),
      '{sis_id}': __("The SIS ID of the invoice's student."),
      '{first_name}': __("The first name of the invoice's student."),
      '{last_name}': __("The last name of the invoice's student."),
    }
    const keyListener = e => {
      if (e.code === 'Backspace') {
        // Would be fun to do something fancy with removing placeholders
        // but for not just leave it how it is
      }
    }
    const getPreview = debounce(async value => {
      if (value) {
        try {
          const { data } = await $http.post('/preview-template', {
            template: value,
          })
          preview.value = data.compiled
        } catch (ex) {
          $error(ex.message)
        }
      }
    }, 500)

    watch(localContent, getPreview)
    getPreview(localContent.value)

    return {
      localContent,
      placeholders,
      keyListener,
      preview,
    }
  }
})
</script>
