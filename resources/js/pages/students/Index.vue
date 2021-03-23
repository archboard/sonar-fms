<template>
  <Authenticated>
    <Table>
      <Thead>
        <tr>
          <th class="w-8 text-left pl-6">
            <Checkbox />
          </th>
          <Th>{{ __('Name') }}</Th>
          <Th>{{ __('Student Number') }}</Th>
          <Th>{{ __('Grade') }}</Th>
        </tr>
      </Thead>
      <Tbody>
        <tr
          v-for="(student, index) in students.data"
          :key="student.id"
        >
          <td class="pl-6 py-4 text-sm">
            <Checkbox
              v-model:checked="user.student_selection"
              @change="selectStudent(student)"
              :value="student.id"
              :id="`student_${student.id}`"
            />
          </td>
          <Td :lighter="false">
            <label :for="`student_${student.id}`" class="cursor-pointer">{{ student.full_name }}</label>
          </Td>
          <Td>{{ student.student_number }}</Td>
          <Td>{{ student.grade_level }}</Td>
        </tr>
      </Tbody>
    </Table>

    <Pagination :meta="students.meta" :links="students.links" />
  </Authenticated>
</template>

<script>
import { defineComponent, inject, nextTick, ref } from 'vue'
import Authenticated from '../../layouts/Authenticated'
import Table from '../../components/tables/Table'
import Thead from '../../components/tables/Thead'
import Th from '../../components/tables/Th'
import Tbody from '../../components/tables/Tbody'
import Td from '../../components/tables/Td'
import Checkbox from '../../components/forms/Checkbox'
import Pagination from '../../components/tables/Pagination'

export default defineComponent({
  components: {
    Pagination,
    Checkbox,
    Td,
    Tbody,
    Th,
    Thead,
    Table,
    Authenticated
  },

  props: {
    students: Object,
    user: Object,
  },

  setup (props) {
    const $http = inject('$http')
    const $route = inject('$route')
    const selectStudent = student => {
      nextTick(() => {
        const add = props.user.student_selection.includes(student.id)
        const method = add ? 'put' : 'delete'

        $http[method]($route('student-selection.update', student.id))
      })
    }

    return {
      selectStudent,
    }
  }
})
</script>
