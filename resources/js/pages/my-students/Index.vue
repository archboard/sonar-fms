<template>
  <Authenticated>
    <Table>
      <Thead>
        <tr>
          <Th>{{ __('Name') }}</Th>
          <Th>{{ __('Student number') }}</Th>
          <Th>{{ __('Grade') }}</Th>
          <Th class="text-right">{{ __('Account balance') }}</Th>
        </tr>
      </Thead>
      <Tbody>
        <tr v-for="student in students" :key="student.uuid">
          <Td>
            <Link :href="`/my-students/${student.uuid}`">{{ student.full_name }}</Link>
          </Td>
          <Td>{{ student.student_number }}</Td>
          <Td>{{ student.grade_level_short_formatted }}</Td>
          <Td class="text-right">{{ student.account_balance_formatted }}</Td>
        </tr>
      </Tbody>
    </Table>
  </Authenticated>
</template>

<script>
import { defineComponent, ref } from 'vue'
import Authenticated from '@/layouts/Authenticated'
import tables from '@/components/tables'
import Link from '@/components/Link'

export default defineComponent({
  components: {
    Link,
    Authenticated,
    ...tables,
  },
  props: {
    students: Array,
  },

  setup () {

  }
})
</script>
