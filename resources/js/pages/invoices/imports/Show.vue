<template>
  <Authenticated>
    <template v-slot:actions>
      <Dropdown
        :menu-items="[
          {
            label: __('Edit import file'),
            route: $route('invoices.imports.edit', invoiceImport),
          },
          {
            label: __('Update mapping'),
            route: $route('invoices.imports.map', invoiceImport),
          },
        ]"
      >
        {{ __('Actions') }}
      </Dropdown>
    </template>

    <Stats
      :stats="[
        {
          label: __('Total records'),
          value: invoiceImport.total_records,
        },
        {
          label: __('Imported records'),
          value: invoiceImport.imported_records,
        },
        {
          label: __('Failed records'),
          value: invoiceImport.failed_records,
        },
      ]"
    />

    <Alert v-if="invoiceImport.imported_at_formatted" class="mt-8">
      {{ __('Invoice imported on :date', { date: invoiceImport.imported_at_formatted }) }}
    </Alert>
    <Alert v-if="invoiceImport.mapping_valid && invoiceImport.imported_records === 0" class="mt-8">
      {{ __('Mapping is ready for import.') }} <inertia-link :href="$route('invoices.imports.start', invoiceImport)" method="post" class="underline">{{ __('Start import') }}</inertia-link>.
    </Alert>
    <Alert v-if="!invoiceImport.mapping_valid" level="warning" class="mt-8">
      {{ __('Mapping is incomplete.') }} <inertia-link :href="$route('invoices.imports.map', invoiceImport)" class="underline">{{ __('Finish mapping') }}</inertia-link>.
    </Alert>

    <Table v-if="results.length > 0" class="mt-8">
      <Thead>
        <tr>
          <Th>{{ __('Row') }}</Th>
          <Th>{{ __('Result') }}</Th>
          <Th></Th>
        </tr>
      </Thead>
      <Tbody>
        <tr
          v-for="result in results"
          :key="result.row"
        >
          <Td
            :class="{
              'bg-red-100 dark:bg-red-600': !result.successful,
            }"
          >{{ result.row }}</Td>
          <Td
            :class="{
              'bg-red-100 dark:bg-red-600': !result.successful,
            }"
          >
            <div v-if="result.successful">
              <ExclamationIcon class="w-4 h-4 mr-1 text-orange-400 dark:text-orange-300 inline-block" v-if="result.warnings.length > 0" />
              {{ __('Invoice created for :student_name', { student_name: result.student }) }}
              <div v-if="result.warnings.length > 0">
                <strong>{{ __('Warnings') }}</strong>
                <ul>
                  <li v-for="warning in result.warnings">{{ __(warning) }}</li>
                </ul>
              </div>
            </div>
            <div v-else>
              {{ __(result.result) }}
            </div>
          </Td>
          <Td
            class="text-right align-top"
            :class="{
              'bg-red-100 dark:bg-red-600': !result.successful,
            }"
          >
            <Link v-if="result.successful" :href="$route('invoices.show', result.result)">{{ __('View invoice') }}</Link>
          </Td>
        </tr>
      </Tbody>
    </Table>
  </Authenticated>
</template>

<script>
import { defineComponent } from 'vue'
import Authenticated from '@/layouts/Authenticated'
import PageProps from '@/mixins/PageProps'
import Dropdown from '@/components/forms/Dropdown'
import Stats from '@/components/Stats'
import Table from '@/components/tables/Table'
import Thead from '@/components/tables/Thead'
import Th from '@/components/tables/Th'
import Tbody from '@/components/tables/Tbody'
import Td from '@/components/tables/Td'
import { ExclamationIcon } from '@heroicons/vue/solid'
import Link from '@/components/Link'
import Alert from '@/components/Alert'

export default defineComponent({
  mixins: [PageProps],
  components: {
    Alert,
    Td,
    Tbody,
    Th,
    Thead,
    Table,
    Stats,
    Authenticated,
    Dropdown,
    ExclamationIcon,
    Link,
  },
  props: {
    invoiceImport: Object,
    results: Array,
  },
})
</script>
