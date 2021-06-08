<template>
  <DatePicker
    v-model="localValue"
    color="pink"
    :is-dark="darkStore.state.isDark"
    mode="dateTime"
    :minute-increment="15"
    :model-config="{ timeAdjust: '00:00:00' }"
    :attributes="attributes"
  >
    <template v-slot="{ inputValue, inputEvents }">
      <div class="relative w-full">
        <div class="absolute top-0 bottom-0 left-0 flex items-center justify-center px-4">
          <CalendarIcon class="w-4 h-4 text-gray-500 dark:text-gray-400" />
        </div>
        <Input :id="id" :model-value="inputValue" v-on="inputEvents" class="px-10" />
        <FadeIn>
          <div class="absolute top-0 bottom-0 right-0 flex items-center justify-center px-4" v-show="localValue">
            <button type="button" @click.prevent="localValue = null" class="focus:outline-none">
              <TrashIcon class="w-4 h-4 text-red-500 dark:text-red-400" />
            </button>
          </div>
        </FadeIn>
      </div>
    </template>
  </DatePicker>
</template>

<script>
import { computed, defineComponent } from 'vue'
import { DatePicker } from 'v-calendar'
import darkStore from '@/stores/theme'
import Input from './Input'
import FadeIn from '../transitions/FadeIn'
import { CalendarIcon, TrashIcon } from '@heroicons/vue/outline'

export default defineComponent({
  components: {
    DatePicker,
    Input,
    CalendarIcon,
    TrashIcon,
    FadeIn,
  },
  props: {
    mode: {
      type: String,
      default: 'dateTime',
    },
    modelValue: [String, Object],
    id: String,
  },
  emits: ['update:modelValue'],

  setup (props, { emit }) {
    const localValue = computed({
      get: () => props.modelValue,
      set: value => emit('update:modelValue', value)
    })
    const attributes = []

    return {
      localValue,
      darkStore,
      attributes,
    }
  }
})
</script>
