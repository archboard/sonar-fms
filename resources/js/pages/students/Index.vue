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
            <Checkbox />
          </td>
          <Td :lighter="false">{{ student.full_name }}</Td>
          <Td>{{ student.student_number }}</Td>
          <Td>{{ student.grade_level }}</Td>
        </tr>
      </Tbody>
    </Table>

    <Pagination :meta="students.meta" :links="students.links" />
  </Authenticated>
</template>

<script>
import { defineComponent, ref } from 'vue'
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
  },

  setup () {

  }
})
</script>
