<template>
  <Listbox v-model="localValue" v-slot="{ open }">
    <div class="relative mt-1">
      <SonarListboxButton>
        <span class="block truncate">{{ selectedCurrency }}</span>

        <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
          <SelectorIcon class="w-5 h-5 text-gray-200" aria-hidden="true" />
        </span>
      </SonarListboxButton>

      <transition
        leave-active-class="transition duration-100 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <SonarListboxOptions>
          <ListboxOption
            v-slot="{ active, selected }"
            v-for="currency in filteredCurrencies"
            :key="currency.id"
            :value="currency"
            as="template"
          >
            <SonarListboxOption :active="active" :selected="selected">
              {{ currency.currency }} ({{ currency.code }})
            </SonarListboxOption>
          </ListboxOption>
        </SonarListboxOptions>
      </transition>
    </div>
  </Listbox>
</template>

<script>
import { computed, defineComponent, inject, ref, watch } from 'vue'
import {
  Listbox,
  ListboxLabel,
  ListboxOption,
} from '@headlessui/vue'
import { CheckIcon, SelectorIcon } from '@heroicons/vue/solid'
import SonarListboxButton from './SonarListboxButton'
import SonarListboxOptions from './SonarListboxOptions'
import SonarListboxOption from './SonarListboxOption'
import Input from './Input'

export default defineComponent({
  components: {
    Input,
    SonarListboxOption,
    SonarListboxOptions,
    SonarListboxButton,
    Listbox,
    ListboxLabel,
    ListboxOption,
    CheckIcon,
    SelectorIcon,
  },
  emits: ['update:modelValue'],
  props: {
    modelValue: {
      type: [String, Number],
    },
    currencies: Array,
  },

  setup (props, { emit }) {
    const findCurrency = id => props.currencies.find(c => c.id === id)
    const $translate = inject('$translate')
    const localValue = ref(findCurrency(props.modelValue))
    const selectedCurrency = computed(() => {
      const currentCurrency = findCurrency(localValue.value?.id)

      return currentCurrency
        ? currentCurrency.currency
        : $translate('Select currency')
    })
    const searchTerm = ref('')
    const filteredCurrencies = computed(() => {
      const term = searchTerm.value.toLowerCase()

      return props.currencies.filter(currency => {
        return currency.currency.toLowerCase().includes(term)
          || currency.code.toLowerCase().includes(term)
      })
    })

    watch(localValue, (newVal) => {
      emit('update:modelValue', newVal.id)
      searchTerm.value = ''
    })

    return {
      localValue,
      searchTerm,
      selectedCurrency,
      filteredCurrencies,
    }
  }
})
</script>
