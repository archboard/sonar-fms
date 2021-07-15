<template>
  <div class="bg-white p-8 text-gray-700 space-y-6 shadow">
    <FadeInGroup>
      <div
        v-for="(row, rowIndex) in localData.rows"
        :key="row.id"
        class="relative"
      >
        <div class="flex items-start space-x-6">
          <FadeInGroup>
            <div
              v-for="(column, columnIndex) in row.columns"
              :key="column"
              class="group relative flex-grow border-4 border-gray-400 border-dashed flex items-center justify-center"
            >
              <div v-if="row.isInvoiceTable">
                This is an invoice table.
              </div>
              <Wysiwyg v-model="column.content" />

              <button
                v-if="row.columns.length > 1"
                class="hidden group-hover:inline-flex items-center justify-center absolute top-0 right-0 p-1 rounded-full focus:outline-none"
                @click.prevent="removeColumn(row, columnIndex, rowIndex)"
              >
                <TrashIcon class="w-6 h-6 text-red-600" />
              </button>
            </div>
          </FadeInGroup>
        </div>

        <button
          class="inline-flex items-center justify-center absolute right-0 bottom-8 translate-x-full p-1 rounded-full focus:outline-none"
          @click.prevent="removeRow(rowIndex)"
        >
          <TrashIcon class="w-6 h-6 text-red-600" />
        </button>

        <button
          class="inline-flex items-center justify-center absolute right-0 bottom-0 translate-x-full p-1 rounded-full focus:outline-none"
          @click.prevent="addColumn(row)"
        >
          <PlusCircleIcon class="w-6 h-6 text-gray-600" />
        </button>
      </div>
    </FadeInGroup>

    <div class="border-4 border-gray-400 border-dashed p-4 flex items-center justify-center space-x-2">
      <Button @click.prevent="() => addRow()" size="sm">
        {{ __('Add content row') }}
      </Button>
      <Button v-if="!hasInvoiceTable" @click.prevent="() => addRow(true)" size="sm">
        {{ __('Add invoice table') }}
      </Button>
    </div>
  </div>

  <pre>{{ localData }}</pre>
</template>

<script>
import { computed, defineComponent, inject, ref, watch } from 'vue'
import Button from '@/components/Button'
import { nanoid } from 'nanoid'
import FadeInGroup from '@/components/transitions/FadeInGroup'
import { TrashIcon, PlusCircleIcon } from '@heroicons/vue/outline'
import Wysiwyg from '@/components/forms/Wysiwyg'

export default defineComponent({
  components: {
    Wysiwyg,
    FadeInGroup,
    Button,
    TrashIcon,
    PlusCircleIcon,
  },
  props: {
    modelValue: Object,
  },
  emits: ['update:modelValue'],

  setup (props, { emit }) {
    const __ = inject('$translate')
    const defaults = {
      rows: [],
    }
    const localData = ref(Object.assign({}, defaults, { ...props.modelValue }))
    const hasInvoiceTable = computed(() => {
      return localData.value.rows.some(r => r.isInvoiceTable)
    })

    watch(() => ({ ...props.modelValue }), state => {
      Object.keys(state).forEach(prop => {
        localData.value[prop] = state[prop]
      })
    })

    const addColumn = (row) => {
      row.columns.push({
        id: nanoid(),
        content: __('<p>Enter your content here...<p>')
      })
    }
    const removeColumn = (row, columnIndex, rowIndex) => {
      row.columns.splice(columnIndex, 1)

      if (row.columns.length === 0) {
        removeRow(rowIndex)
      }
    }
    const addRow = (isInvoiceTable = false) => {
      const row = {
        id: nanoid(),
        columns: [],
        isInvoiceTable,
      }
      addColumn(row)

      localData.value.rows.push(row)
    }
    const removeRow = rowIndex => {
      localData.value.rows.splice(rowIndex, 1)
    }

    return {
      localData,
      addRow,
      removeRow,
      addColumn,
      hasInvoiceTable,
      removeColumn,
    }
  }
})
</script>
