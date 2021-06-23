<template>
  <ul
    v-if="files.length > 0"
    class="border border-gray-200 dark:border-gray-500 rounded-md divide-y divide-gray-200 dark:divide-gray-500"
    :class="{
      'mb-5': files.length > 0 && multiple
    }"
  >
    <li
      v-for="(file, index) in files"
      class="pl-3 pr-4 py-3 flex items-center justify-between text-sm"
    >
      <div class="w-0 flex-1 flex items-center">
        <PaperClipIcon class="flex-shrink-0 h-5 w-5 text-gray-400" aria-hidden="true" />
        <span class="ml-2 flex-1 w-0 truncate">
          {{ file.name }}
        </span>
      </div>
      <div class="ml-4 flex-shrink-0">
        <a @click.prevent="files.splice(index, 1)" href="#" class="font-medium text-primary-600 hover:text-primary-500 dark:text-primary-500 dark:hover:text-primary-400 transition">
          {{ __('Remove') }}
        </a>
      </div>
    </li>
  </ul>

  <div
    v-if="files.length === 0 || multiple"
    class="flex justify-center px-6 pt-5 pb-6 border-2 rounded-md"
    :class="dragClasses"
    @dragover.prevent="dragClasses = `bg-white dark:bg-gray-700`"
    @dragleave.prevent="dragClasses = defaultDragClass"
    @drop.prevent="handleDrop"
  >
    <div class="space-y-1 text-center">
      <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
      </svg>
      <div class="flex text-sm text-gray-600 dark:text-gray-300">
        <label :for="id" class="relative cursor-pointer bg-transparent rounded-md font-medium text-primary-600 hover:text-primary-500 dark:text-primary-500 dark:hover:text-primary-400 focus-within:outline-none transition">
          <span>{{ __('Upload a file') }}</span>
          <input ref="file" :id="id" name="file-upload" type="file" class="sr-only" :multiple="multiple" @change="fileSelected" />
        </label>
        <p class="pl-1">{{ __('or drag and drop') }}</p>
      </div>
      <p v-if="extensions.length > 0" class="text-xs text-gray-500 dark:text-gray-400">
        {{ extensions.join(', ') }}
      </p>
    </div>
  </div>
</template>

<script>
import { defineComponent, inject, ref, watch } from 'vue'
import { nanoid } from 'nanoid'
import { PaperClipIcon } from '@heroicons/vue/solid'

export default defineComponent({
  components: {
    PaperClipIcon,
  },

  props: {
    extensions: {
      type: Array,
      default: [],
    },
    modelValue: [Object, String],
    id: {
      type: String,
      default: 'file-upload',
    },
    multiple: {
      type: Boolean,
      default: false
    }
  },
  emits: ['update:modelValue'],

  setup (props, { emit }) {
    const $error = inject('$error')
    const $translate = inject('$translate')
    const file = ref()
    const files = ref([])
    const defaultDragClass = 'border-gray-300 dark:border-gray-500 border-dashed'
    const dragClasses = ref(defaultDragClass)
    const fileSelected = e => {
      files.value = Array.from(e.target.files).map(fileMap)
    }
    const handleDrop = e => {
      console.log(e)
      dragClasses.value = defaultDragClass
      const dataFiles = e.dataTransfer.items || e.dataTransfer.files

      try {
        const chosenFiles = Array.from(dataFiles)
          .map(item => typeof item.getAsFile === 'function' ? item.getAsFile() : item)
          .map(fileMap)
          .filter(item => item.id)

        files.value = props.multiple
          ? [...files.value, ...chosenFiles]
          : [chosenFiles.shift()]
      } catch (err) {
        $error($translate('Could not choose file: :message', {
          message: err.message
        }))
      }
    }
    const hasValidExtension = extension => {
      return props.extensions.length > 0 &&
        props.extensions.map(ex => ex.toLowerCase()).includes(extension.toLowerCase())
    }
    const fileMap = (file) => {
      if (!file.type) {
        $error($translate('Please select a valid file.'))
        return {}
      }

      const [nameParts, extension] = file.name.split('.')

      if (!hasValidExtension(extension)) {
        $error($translate('Invalid file extension (:extensions only).', { extensions: props.extensions.join(', ') }))
        return {}
      }

      return {
        file,
        name: file.name,
        progress: 0,
        id: nanoid(),
      }
    }
    watch(files, () => {
      emit('update:modelValue', files.value)
    })

    return {
      fileSelected,
      file,
      files,
      dragClasses,
      handleDrop,
      defaultDragClass,
    }
  }
})
</script>
