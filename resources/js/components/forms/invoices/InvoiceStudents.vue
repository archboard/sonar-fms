<template>
  <div class="mb-6">
    <CardSectionHeader>
      {{ __('Students') }}<req />
    </CardSectionHeader>
    <HelpText class="text-sm mt-1">
      {{ __('Add or remove the students for which this invoice will be created.') }}
    </HelpText>
  </div>

  <Error v-if="form.errors.students">
    {{ form.errors.students }}
  </Error>

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
          <Td :lighter="false">
            <a :href="$route('students.show', student)" class="hover:underline" target="_blank">
              {{ student.full_name }}
            </a>
          </Td>
          <Td>{{ student.student_number }}</Td>
          <Td>{{ student.grade_level_formatted }}</Td>
          <Td class="text-right">
            <div class="flex items-center justify-end space-x-2">
              <a :href="$route('students.show', student)" class="text-primary-600 dark:text-primary-500 hover:text-primary-500 dark:hover:text-primary-300 focus:outline-none transition" target="_blank">
                <ExternalLinkIcon class="w-4 h-4" />
              </a>
              <button v-if="allowStudentEditing" @click.prevent="removeStudent" class="text-red-600 dark:text-red-500 hover:text-red-500 dark:hover:text-red-300 focus:outline-none transition" type="button">
                <TrashIcon class="w-4 h-4" />
              </button>
            </div>
          </Td>
        </tr>
      </Tbody>
    </Table>
  </FadeIn>

  <AddThingButton v-if="allowStudentEditing" @click="addStudent = true">
    {{ __('Add students') }}
  </AddThingButton>

  <StudentSearchModal
    v-if="addStudent"
    @close="addStudent = false"
    @selected="studentSelected"
  />
</template>

<script>
import { defineComponent, ref, watchEffect } from 'vue'
import invoiceFormComponent from '@/composition/invoiceFormComponent.js'
import InvoiceFormCollection from '@/mixins/InvoiceFormCollection'
import tables from '@/components/tables.vue'
import fetchesStudents from '@/composition/fetchesStudents.js'
import { TrashIcon, ExternalLinkIcon } from '@heroicons/vue/outline'
import AddThingButton from '@/components/forms/AddThingButton.vue'
import Error from '@/components/forms/Error.vue'
import StudentSearchModal from '@/components/modals/StudentSearchModal.vue'
import Req from '@/components/forms/Req.vue'

export default defineComponent({
  mixins: [InvoiceFormCollection],
  components: {
    Req,
    StudentSearchModal,
    Error,
    AddThingButton,
    ...tables,
    TrashIcon,
    ExternalLinkIcon,
  },
  props: {
    allowStudentEditing: {
      type: Boolean,
      default: true,
    },
  },

  setup (props, context) {
    const { localValue } = invoiceFormComponent(props, context)
    const { students, search, fetchingStudents } = fetchesStudents()
    const addStudent = ref(props.allowStudentEditing && localValue.value.length === 0)
    const removeStudent = studentId => {
      const index = localValue.value.findIndex(id => id === studentId)
      localValue.value.splice(index, 1)
    }
    const studentSelected = student => {
      if (typeof student === 'object') {
        localValue.value.push(student.id)
      } else {
        localValue.value.push(student)
      }
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
      studentSelected,
    }
  }
})
</script>
