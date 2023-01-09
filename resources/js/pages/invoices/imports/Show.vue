<template>
  <Authenticated>
    <template v-slot:actions>
      <Dropdown size="sm">
        {{ __('Actions') }}

        <template v-slot:dropdown>
          <InvoiceImportActions
            :invoice-import="invoiceImport"
            @import="importingImport = invoiceImport"
            @rollback="rollingBackImport = invoiceImport"
            @template="convert = true"
          />
        </template>
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
    <Alert v-if="invoiceImport.mapping_valid && !invoiceImport.imported_at_formatted && invoiceImport.imported_records === 0" class="mt-8" level="success">
      {{ __('Mapping is ready for import.') }} <button @click.prevent="importingImport = invoiceImport" class="font-medium hover:underline focus:outline-none">{{ __('Start import') }}</button>.
    </Alert>
    <Alert v-if="!invoiceImport.mapping_valid" level="warning" class="mt-8">
      {{ __('Mapping is incomplete.') }} <inertia-link :href="$route('invoices.imports.map', invoiceImport)" class="underline">{{ __('Finish mapping') }}</inertia-link>.
    </Alert>
    <Alert v-if="isPreview" class="mt-8">
      {{ __('This is a preview of what would be imported.') }}  <button @click.prevent="importingImport = invoiceImport" class="font-medium hover:underline focus:outline-none">{{ __('Start import') }}</button>
    </Alert>

    <Table v-if="results.length > 0" class="mt-8">
      <Thead>
        <tr>
          <Th>{{ __('Row') }}</Th>
          <Th>{{ __('Result') }}</Th>
          <Th class="text-right">
            <span v-if="isPreview">
              {{ __('Amount due') }}
            </span>
          </Th>
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
            <Link v-if="!isPreview && result.successful" :href="$route('invoices.show', result.result)">{{ __('View invoice') }}</Link>
            <span v-else-if="isPreview && result.successful" class="font-medium">
              {{ displayCurrency(previewResults[result.result].amount_due) }}
            </span>
          </Td>
        </tr>
      </Tbody>
    </Table>
  </Authenticated>

  <ConfirmationModal
    v-if="rollingBackImport.id"
    @close="rollingBackImport = {}"
    @confirmed="rollBack(`/invoices/imports/${rollingBackImport.id}/reverse`)"
  />
  <ConfirmationModal
    v-if="importingImport.id"
    @close="importingImport = {}"
    @confirmed="importImport(`/invoices/imports/${importingImport.id}/start`)"
  >
    <template v-slot:content>
      {{ __('This will begin importing invoices.') }}
    </template>
  </ConfirmationModal>
  <ConvertImportMappingToTemplateModal
    v-if="convert"
    @close="convert = false"
    :endpoint="`/invoices/imports/${invoiceImport.id}/template`"
  />
</template>

<script>
import { defineComponent, ref } from 'vue'
import Authenticated from '@/layouts/Authenticated.vue'
import PageProps from '@/mixins/PageProps'
import Dropdown from '@/components/forms/Dropdown.vue'
import Stats from '@/components/Stats.vue'
import Table from '@/components/tables/Table.vue'
import Thead from '@/components/tables/Thead.vue'
import Th from '@/components/tables/Th.vue'
import Tbody from '@/components/tables/Tbody.vue'
import Td from '@/components/tables/Td.vue'
import { ExclamationIcon } from '@heroicons/vue/solid'
import Link from '@/components/Link.vue'
import Alert from '@/components/Alert.vue'
import { MenuItem } from '@headlessui/vue'
import SonarMenuItem from '@/components/forms/SonarMenuItem.vue'
import ScaleIn from '@/components/transitions/ScaleIn.vue'
import SonarMenuItems from '@/components/dropdown/SonarMenuItems.vue'
import rollsBackImport from '@/composition/rollsBackImport.js'
import ConfirmationModal from '@/components/modals/ConfirmationModal.vue'
import checksPermissions from '@/composition/checksPermissions.js'
import importsInvoiceImport from '@/composition/importFileImport.js'
import displaysCurrency from '@/composition/displaysCurrency.js'
import ConvertImportMappingToTemplateModal from '@/components/modals/ConvertImportMappingToTemplateModal.vue'
import InvoiceImportActions from '@/components/InvoiceImportActions.vue'

export default defineComponent({
  mixins: [PageProps],
  components: {
    InvoiceImportActions,
    ConvertImportMappingToTemplateModal,
    ConfirmationModal,
    SonarMenuItems,
    ScaleIn,
    SonarMenuItem,
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
    MenuItem,
  },
  props: {
    invoiceImport: Object,
    results: {
      type: Array,
      default: () => ([])
    },
    isPreview: {
      type: Boolean,
      default: false,
    },
    previewResults: {
      type: Object,
      default: () => ({})
    },
  },

  setup (props) {
    const { rollingBackImport, rollBack } = rollsBackImport()
    const { importImport, importingImport } = importsInvoiceImport()
    const { can } = checksPermissions(props.permissions)
    const { displayCurrency } = displaysCurrency()
    const convert = ref(false)

    return {
      rollingBackImport,
      rollBack,
      can,
      importingImport,
      importImport,
      displayCurrency,
      convert,
    }
  },
})
</script>
