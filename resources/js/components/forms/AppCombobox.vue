<template>
  <div class="relative">
    <Combobox v-model="localValue" :disabled="disabled">
      <div
        class="relative w-full cursor-default"
      >
        <ComboboxInput
          :class="input"
          @change="$emit('update:query', $event.target.value)"
          :display-value="handleDisplay"
          :disabled="disabled"
          :placeholder="placeholder || __('Type for options...')"
        />
        <ComboboxButton class="absolute inset-y-0 right-0 flex items-center" :class="{ 'pr-2': !hasError, 'pr-10': hasError }">
          <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
          </svg>

        </ComboboxButton>
      </div>
      <transition
        enter-active-class="transition duration-100 ease-out"
        enter-from-class="transform scale-95 opacity-0"
        enter-to-class="transform scale-100 opacity-100"
        leave-active-class="transition duration-75 ease-out"
        leave-from-class="transform scale-100 opacity-100"
        leave-to-class="transform scale-95 opacity-0"
      >
        <ComboboxOptions
          class="absolute z-10 mt-1 top-10 max-h-60 w-full min-w-[16rem] overflow-auto rounded-md shadow-md bg-white dark:bg-gray-700 text-base ring-opacity-5 focus:outline-none sm:text-sm"
          :class="{
            'py-1 shadow-lg ring-1 ring-black': !!query
          }"
          :hold="true"
        >
          <div
            v-if="options.length === 0 && query !== ''"
            class="relative cursor-default select-none py-2 px-4 text-gray-700"
          >
            {{ $t('Nothing found.') }}
          </div>

          <ComboboxOption
            as="template"
            v-slot="{ active, selected }"
            v-for="item in options"
            :key="item.id"
            :value="item"
          >
            <li
              class="relative cursor-default select-none py-2 pl-10 pr-4"
              :class="[active || selected ? classes.active : classes.inactive, classes.always]"
            >
              <span
                class="block truncate"
                :class="{ 'font-medium': selected, 'font-normal': !selected }"
              >
                <slot name="item" :selected="selected" :active="active" :item="item">
                  {{ handleDisplay(item) }}
                </slot>
              </span>
              <span
                v-if="selected"
                class="absolute inset-y-0 left-0 flex items-center pl-3"
              >
                <CheckIcon class="h-5 w-5" />
              </span>
            </li>
          </ComboboxOption>
        </ComboboxOptions>
      </transition>
    </Combobox>
  </div>
</template>

<script setup>
import {
  Combobox,
  ComboboxInput,
  ComboboxOptions,
  ComboboxOption,
  ComboboxButton,
} from '@headlessui/vue'
import { useVModel } from '@vueuse/core'
import { CheckIcon } from '@heroicons/vue/outline'
import menuItemClasses from '@/composition/menuItemClasses.js'
import inputClasses from '@/composition/inputClasses.js'

const props = defineProps({
  modelValue: [Number, String, Object],
  query: {
    type: String,
  },
  options: {
    type: Array,
    required: true,
  },
  displayValue: Function,
  hasError: {
    type: Boolean,
    default: () => false,
  },
  disabled: {
    type: Boolean,
    default: () => false,
  },
  placeholder: {
    type: String,
  }
})
const emit = defineEmits(['update:modelValue', 'update:query'])
const localValue = useVModel(props, 'modelValue', emit)
const classes = menuItemClasses()
const { input } = inputClasses()

const handleDisplay = item => {
  if (typeof props.displayValue === 'function') {
    return props.displayValue(item) || ''
  }

  return item?.name || item?.title || item?.label || ''
}
</script>
