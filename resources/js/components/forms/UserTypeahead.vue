<template>
  <Typeahead
    v-model="invoiceDisplay"
    :items="invoiceOptions"
    :placeholder="__('Search by name or email')"
    @selected="invoiceSelected"
    @blur="onBlur"
  >
    <template v-slot:item="{ item, active }">
      <div
        class="flex items-center justify-between rounded w-full px-3 py-1.5 truncate"
        :class="{
          'bg-gradient-to-bl from-fuchsia-500 to-fuchsia-600 dark:from-fuchsia-600 dark:to-fuchsia-700 text-white': active
        }"
      >
        <div class="flex items-center space-x-2">
          <span>{{ item.full_name }}</span>
        </div>
      </div>
    </template>
  </Typeahead>
</template>

<script>
import { defineComponent, inject, ref, watch, computed, nextTick } from 'vue'
import hasModelValue from '@/composition/hasModelValue'
import cloneDeep from 'lodash/cloneDeep'
import Typeahead from '@/components/forms/Typeahead'
import InvoiceStatusBadge from '@/components/InvoiceStatusBadge'
import debounce from 'lodash/debounce'

export default defineComponent({
  components: {
    InvoiceStatusBadge,
    Typeahead,
  },
  props: {
    modelValue: [Object],
    invoice: {
      type: Object,
      default: () => ({})
    }
  },
  emits: ['update:modelValue'],

  setup (props, { emit }) {
    const $http = inject('$http')
    const { localValue } = hasModelValue(props, emit)
    const searchTerm = ref('')
    const selectedUser = ref(cloneDeep(localValue.value))
    const invoiceOptions = ref([])
    const invoiceDisplay = computed({
      get: () => (!searchTerm.value && selectedUser.value.id)
          ? selectedUser.value.full_name
          : searchTerm.value,
      set: value => {
        searchTerm.value = value
      }
    })
    const invoiceSelected = item => {
      nextTick(() => {
        searchTerm.value = ''

        const user = item || {}
        selectedUser.value = user
        localValue.value = user
      })
    }
    const onBlur = () => {
      // If there isn't a search value on blur,
      // assume that the value is cleared out
      // and we need to remove the value
      if (!searchTerm.value) {
        localValue.value = {}
      }
    }

    watch(searchTerm, debounce(async value => {
      if (!value) {
        invoiceOptions.value = []
        return
      }

      const { data } = await $http.post('/search/users', {
        s: value,
      })

      invoiceOptions.value = data
    }, 500))

    return {
      searchTerm,
      invoiceOptions,
      invoiceDisplay,
      invoiceSelected,
      onBlur,
    }
  }
})
</script>
