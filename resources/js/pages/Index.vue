<template>
  <Authenticated>
    <template #actions>
      <Dropdown
        v-if="can('invoices.create')"
        size="sm"
        :menu-items="[
          ...invoiceLinks
        ]"
      >
        {{ __('Quick links') }}
      </Dropdown>
    </template>

    <section class="space-y-8">
      <div v-if="newStudents.length > 0">
        <div class="pb-5 border-b border-gray-200 dark:border-gray-700">
          <CardHeader>{{ __('New students') }}</CardHeader>
          <HelpText>{{ __('These are the ten newest students in your school.') }}</HelpText>
        </div>
        <div class="pt-5">
          <Table>
            <Thead>
              <tr>
                <Th>{{ __('Name') }}</Th>
                <Th>{{ __('Student number') }}</Th>
                <Th>{{ __('Grade') }}</Th>
                <Th class="text-right">{{ __('Account balance') }}</Th>
                <th></th>
              </tr>
            </Thead>
            <Tbody>
              <tr v-for="student in newStudents" :key="student.uuid">
                <Td>
                  <Link :href="`/students/${student.uuid}`">{{ student.full_name }}</Link>
                </Td>
                <Td>{{ student.student_number }}</Td>
                <Td>{{ student.grade_level_short_formatted }}</Td>
                <Td class="text-right">{{ student.account_balance_formatted }}</Td>
                <Td class="text-right">
                  <VerticalDotMenu>
                    <StudentActionItems :student="student" />
                  </VerticalDotMenu>
                </Td>
              </tr>
            </Tbody>
          </Table>
        </div>
      </div>

      <div v-if="myStudents.length > 0">
        <div class="pb-5 border-b border-gray-200 dark:border-gray-700">
          <CardHeader>{{ __('My students') }}</CardHeader>
          <HelpText>{{ __('These are the students with whom you have a relationship.') }}</HelpText>
        </div>
        <div class="pt-5">
          <MyStudents :students="myStudents" />
        </div>
      </div>
    </section>
  </Authenticated>
</template>

<script>
import { defineComponent, inject, ref } from 'vue'
import Authenticated from '../layouts/Authenticated'
import CardWrapper from '@/components/CardWrapper'
import CardPadding from '@/components/CardPadding'
import CardHeader from '@/components/CardHeader'
import Link from '@/components/Link'
import Dropdown from '@/components/forms/Dropdown'
import MyStudents from '@/components/tables/MyStudents'
import HelpText from '@/components/HelpText'
import checksPermissions from '@/composition/checksPermissions'
import tables from '@/components/tables'
import VerticalDotMenu from '@/components/dropdown/VerticalDotMenu'
import SonarMenuItem from '@/components/forms/SonarMenuItem'
import StudentActionItems from '@/components/StudentActionItems'

export default defineComponent({
  components: {
    StudentActionItems,
    SonarMenuItem,
    VerticalDotMenu,
    HelpText,
    MyStudents,
    Dropdown,
    Link,
    CardHeader,
    CardPadding,
    CardWrapper,
    Authenticated,
    ...tables,
  },
  props: {
    myStudents: {
      type: Array,
      default: () => ([])
    },
    newStudents: {
      type: Array,
      default: () => ([])
    },
  },

  setup () {
    const { can } = checksPermissions()
    const __ = inject('$translate')
    const invoiceLinks = can('invoices.create') ? [
      {
        label: __('Create an invoice'),
        route: `/invoices/create`,
      },
      {
        label: __('Import invoices'),
        route: `/invoices/imports/create`,
      },
    ] : []

    return {
      can,
      invoiceLinks,
    }
  }
})
</script>
