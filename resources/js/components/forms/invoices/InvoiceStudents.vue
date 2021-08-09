<template>
  <div class="mb-6">
    <CardSectionHeader>
      {{ __('Students') }}
    </CardSectionHeader>
    <HelpText class="text-sm mt-1">
      {{ __('Add or remove the students for which this invoice will be created.') }}
    </HelpText>
  </div>

  <Alert v-if="form.errors.students" level="error">
    {{ form.errors.students }}
  </Alert>

  <FadeIn>
    <Table v-if="students.length > 0">
      <Thead>
        <tr>
          <Th>{{ __('Name') }}</Th>
          <Th>{{ __('Student number') }}</Th>
          <Th>{{ __('Grade') }}</Th>
          <Th></Th>
        </tr>
      </Thead>
      <Tbody>
        <tr
          v-for="student in students"
          :key="student.id"
        >
          <Td :lighter="false">{{ student.full_name }}</Td>
          <Td>{{ student.student_number }}</Td>
          <Td>{{ student.grade_level_formatted }}</Td>
          <Td class="text-right">
            <div class="flex items-center justify-end">
              <button @click.prevent="removeStudent" class="text-red-600 dark:text-red-500 hover:text-red-500 dark:hover:text-red-300 focus:outline-none transition" type="button">
                <TrashIcon class="w-4 h-4" />
              </button>
            </div>
          </Td>
        </tr>
      </Tbody>
    </Table>
  </FadeIn>

<!--  <AddThingButton @click="addStudent = true">-->
<!--    {{ __('Add students') }}-->
<!--  </AddThingButton>-->
</template>

<script>
import { defineComponent, ref, watchEffect } from 'vue'
import invoiceFormComponent from '@/composition/invoiceFormComponent'
import InvoiceFormCollection from '@/mixins/InvoiceFormCollection'
import tables from '@/components/tables'
import fetchesStudents from '@/composition/fetchesStudents'
import { TrashIcon } from '@heroicons/vue/outline'
import AddThingButton from '@/components/forms/AddThingButton'
import Alert from '@/components/Alert'

export default defineComponent({
  mixins: [InvoiceFormCollection],
  components: {
    Alert,
    AddThingButton,
    ...tables,
    TrashIcon,
  },

  setup (props, context) {
    const { localValue } = invoiceFormComponent(props, context)
    const { students, search, fetchingStudents } = fetchesStudents()
    const addStudent = ref(false)
    const removeStudent = studentId => {
      const index = localValue.value.findIndex(id => id === studentId)
      localValue.value.splice(index, 1)
    }
    watchEffect(() => {
      search({ ids: localValue.value })
    })

    return {
      localValue,
      addStudent,
      students,
      removeStudent,
      fetchingStudents,
    }
  }
})
</script>
