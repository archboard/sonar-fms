<template>
  <Combobox as="div" v-slot="{ open }" class="relative" v-model="localValue">
    <ComboboxInput
      ref="comboInput"
      @change="query = $event.target.value"
      :class="input"
      :display-value="(user) => user.full_name"
      :id="id"
      :placeholder="__('Search by name or email')"
    />
    <DropIn>
      <ComboboxOptions class="absolute z-10 origin-top-left p-1 mt-2 w-full space-y-1 rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 focus:outline-none">
        <ComboboxOption
          as="template"
          v-for="user in users"
          :key="user.id"
          :value="user"
          v-slot="{ active, selected }"
        >
          <li
            :class="[
              (active || selected) ? classes.active : classes.inactive,
              classes.always,
              'flex space-x-2',
            ]"
          >
            <span>{{ user.full_name }}</span>
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
    const users = ref([])
    const comboInput = ref()
    const search = debounce(async (term) => {
       const { data } = await $http.post('/search/users', {
        s: term,
      })
      users.value = data
    }, 250)
    watch(query, search)

    return {
      users,
      input,
      localValue,
      classes,
      comboInput,
      query,
      fetching,
    }
  }
})
</script>
