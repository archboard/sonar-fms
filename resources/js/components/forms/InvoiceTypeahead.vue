<template>
  <Typeahead
    v-model="invoiceDisplay"
    :items="invoiceOptions"
    :placeholder="__('Search for invoice by title, number or student')"
    @selected="invoiceSelected"
    @blur="onBlur"
    :disabled="disabled"
  >
    <template v-slot:item="{ item, active }">
      <div
        class="flex items-center justify-between rounded w-full px-3 py-1.5 truncate"
        :class="{
          'bg-gradient-to-bl from-fuchsia-500 to-fuchsia-600 dark:from-fuchsia-600 dark:to-fuchsia-700 text-white': active
        }"
      >
        <div class="flex items-center space-x-2">
          <span>{{ item.title }} ({{ item.invoice_number }})</span>
          <div
            class="flex items-center space-x-2"
            :class="{
                'text-gray-600 dark:text-gray-300': !active,
                'text-gray-100 dark:text-gray-200': active,
            }"
          >
            <InvoiceStatusBadge :invoice="item" />
            <span aria-hidden="true">/</span>
            <span class="text-sm">{{ item.amount_due_formatted }}</span>
            <span aria-hidden="true">/</span>
            <span class="text-sm">{{ item.student_list }}</span>
          </div>
        </div>
      </div>
    </template>
  </Typeahead>
</template>

<script>
import { defineComponent, inject, ref, watch, computed, nextTick } from 'vue'
import hasModelValue from '@/composition/hasModelValue.js'
import cloneDeep from 'lodash/cloneDeep'
import Typeahead from '@/components/forms/Typeahead.vue'
import InvoiceStatusBadge from '@/components/InvoiceStatusBadge.vue'
import debounce from 'lodash/debounce'

export default defineComponent({
  components: {
    InvoiceStatusBadge,
    Typeahead,
  },
  props: {
    modelValue: [Object],
    disabled: {
      type: Boolean,
      default: false,
    },
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
    const selectedInvoice = ref(cloneDeep(localValue.value))
    const invoiceOptions = ref([])
    const invoiceDisplay = computed({
      get: () => selectedInvoice.value.uuid
          ? `${selectedInvoice.value.title} (${selectedInvoice.value.invoice_number})`
          : searchTerm.value,
      set: value => {
        searchTerm.value = value
      }
    })
    const invoiceSelected = item => {
      nextTick(() => {
        const invoice = item || {}

        selectedInvoice.value = invoice
        localValue.value = invoice
      })
    }
    const onBlur = () => {
      // If there isn't a search value on blur,
      // assume that the value is cleared out
      // so we need to remove the value
      if (!searchTerm.value) {
        localValue.value = {}
      }
    }

    watch(searchTerm, debounce(async value => {
      if (!value) {
        invoiceOptions.value = []
        return
      }

      const { data } = await $http.post('/search/invoices', {
        s: value,
        status: ['unpaid', 'published'],
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
