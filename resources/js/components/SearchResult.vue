<template>
  <InertiaLink
    :href="result.url"
    class="flex items-center justify-between rounded w-full px-3 py-1.5 truncate"
    :class="{
      'bg-gradient-to-bl from-fuchsia-500 to-fuchsia-600 dark:from-fuchsia-600 dark:to-fuchsia-700 text-white': active
    }"
  >
    <div class="flex items-center space-x-2">
      <span>{{ result.title }}</span>
      <div
        v-if="result.type === 'invoices'"
        class="flex items-center space-x-1 text-gray-600 text-sm"
        :class="{
            'text-gray-600 dark:text-gray-300': !active,
            'text-gray-100 dark:text-gray-200': active,
        }"
      >
        <InvoiceStatusBadge :invoice="result.searchable" />
        <span>-</span>
        <span>{{ result.searchable.amount_due_formatted }}</span>
        <span>-</span>
        <span>{{ result.searchable.student_list }}</span>
      </div>
      <div
        v-else-if="result.type === 'students'"
        class="flex items-center space-x-1 text-sm"
        :class="{
          'text-gray-600 dark:text-gray-300': !active,
          'text-gray-100 dark:text-gray-200': active,
        }"
      >
        <div v-if="result.searchable.enrolled" class="flex items-center space-x-2">
          <CheckCircleIcon
            class="h-4 w-4"
            :class="{
              'text-green-500': !active,
              'text-green-200': active,
            }"
          />
          <span
            :class="{
              'text-green-700 dark:text-green-400': !active,
              'text-green-100': active,
            }"
          >{{ __('Enrolled') }}</span>
        </div>
        <div v-else class="flex items-center space-x-2">
          <XCircleIcon
            class="h-4 w-4"
            :class="{
              'text-yellow-500': !active,
              'text-yellow-200': active,
            }"
          />
          <span
            :class="{
              'text-yellow-700 dark:text-yellow-400': !active,
              'text-yellow-100': active,
            }"
          >{{ __('Not enrolled') }}</span>
        </div>
        <span>-</span>
        <span>{{ result.searchable.student_number }}</span>
        <span>-</span>
        <span>{{ result.searchable.grade_level_formatted }}</span>
      </div>
    </div>
    <span class="inline-flex items-center font-mono text-xs py-0.5 px-1 border border-gray-200 dark:border-gray-500 rounded text-white bg-gray-500 dark:bg-gray-800">↵ Enter</span>
  </InertiaLink>
</template>

<script>
import { defineComponent } from 'vue'
import { XCircleIcon, CheckCircleIcon } from '@heroicons/vue/outline'
import InvoiceStatusBadge from '@/components/InvoiceStatusBadge'

export default defineComponent({
  components: {
    XCircleIcon,
    CheckCircleIcon,
    InvoiceStatusBadge,
  },
  props: {
    result: Object,
    active: Boolean,
  },

  setup (props, context) {
    return {}
  },
})
</script>
