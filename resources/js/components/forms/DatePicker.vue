<template>
  <DatePicker
    v-model="localValue"
    color="pink"
    :is-dark="darkStore.state.isDark"
    :mode="mode"
    :minute-increment="15"
    :model-config="modelConfig"
    :attributes="attributes"
    :timezone="timezone"
    :input-debounce="250"
  >
    <template v-slot="{ inputValue, inputEvents, togglePopover }">
      <div class="relative w-full">
        <div class="absolute top-0 bottom-0 left-0 flex items-center justify-center px-4 cursor-pointer" @click="togglePopover()">
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
import Input from '@/components/forms/Input.vue'
import FadeIn from '@/components/transitions/FadeIn.vue'
import { CalendarIcon, TrashIcon } from '@heroicons/vue/outline'
import displaysDate from '@/composition/displaysDate.js'
import 'v-calendar/dist/style.css'

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
    const { timezone, getDate } = displaysDate()
    const localValue = computed({
      get: () => !props.modelValue ? null : (getDate(props.modelValue)?.toDate() || null),
      set: value => emit('update:modelValue', value)
    })
    const attributes = []
    const modelConfig = {
      timeAdjust: '00:00:00'
    }

    return {
      localValue,
      darkStore,
      attributes,
      timezone,
      modelConfig,
    }
  }
})
</script>
