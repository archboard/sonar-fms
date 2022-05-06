<template>
  <Combobox as="div" v-slot="{ open }" class="relative" v-model="localValue">
    <ComboboxInput
      @change="query = $event.target.value"
      :class="input"
      :display-value="(family) => family.name || ''"
      :id="id"
      :placeholder="__('Search by family name or student family member')"
    />
    <DropIn>
      <ComboboxOptions
        v-slot="{ open }"
        class="absolute z-10 origin-top-left mt-2 w-full space-y-1 rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 focus:outline-none"
        :class="{
          'p-1': open && families.length > 0,
        }"
      >
        <ComboboxOption
          as="template"
          v-for="family in families"
          :key="family.id"
          :value="family"
          v-slot="{ active, selected }"
        >
          <li
            :class="[
              (active || selected) ? classes.active : classes.inactive,
              classes.always,
              'flex space-x-2',
            ]"
          >
            <span>{{ family.name }}</span>
          </li>
        </ComboboxOption>
      </ComboboxOptions>
    </DropIn>
  </Combobox>
</template>

<script>
import { defineComponent, inject, ref, watch } from 'vue'
import { Combobox, ComboboxInput, ComboboxOption, ComboboxOptions } from '@headlessui/vue'
import hasModelValue from '@/composition/hasModelValue'
import menuItemClasses from '@/composition/menuItemClasses'
import InvoiceStatusBadge from '@/components/InvoiceStatusBadge'
import inputClasses from '@/composition/inputClasses'
import debounce from 'lodash/debounce'
import DropIn from '@/components/transitions/DropIn'

export default defineComponent({
  components: {
    DropIn,
    InvoiceStatusBadge,
    Combobox,
    ComboboxInput,
    ComboboxOption,
    ComboboxOptions,
  },
  props: {
    modelValue: [Object],
    id: String,
  },
  emits: ['update:modelValue'],

  setup (props, { emit }) {
    const { input } = inputClasses()
    const { localValue } = hasModelValue(props, emit)
    const classes = menuItemClasses()
    const $http = inject('$http')
    const fetching = ref(false)
    const query = ref('')
    const families = ref([])
    const search = debounce(async (term) => {
       const { data } = await $http.post('/search/families', {
        s: term,
      })
      families.value = data
    }, 250)
    watch(query, search)

    return {
      families,
      input,
      localValue,
      classes,
      query,
      fetching,
    }
  }
})
</script>
