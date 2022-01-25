<template>
  <FadeInGroup>
    <DismissibleBadge v-if="filters.date_start" @dismiss="filters.date_start = null">
      {{ __('After :date', { date: displayDate(filters.date_start, `MMM D, YYYY`) }) }}
    </DismissibleBadge>
    <DismissibleBadge v-if="filters.date_end" @dismiss="filters.date_end = null">
      {{ __('Before :date', { date: displayDate(filters.date_end, `MMM D, YYYY`) }) }}
    </DismissibleBadge>

    <DismissibleBadge v-if="filters.due_start" @dismiss="filters.due_start = null">
      {{ __('Due after :date', { date: displayDate(filters.due_start, `MMM D, YYYY`) }) }}
    </DismissibleBadge>
    <DismissibleBadge v-if="filters.due_end" @dismiss="filters.due_end = null">
      {{ __('Due before :date', { date: displayDate(filters.due_end, `MMM D, YYYY`) }) }}
    </DismissibleBadge>

    <DismissibleBadge
      v-for="(status, index) in filters.status"
      :key="status"
      @dismiss="filters.status.splice(index, 1)"
    >
      {{ statuses[status] }}
    </DismissibleBadge>

    <DismissibleBadge
      v-for="(grade, index) in filters.grades"
      :key="grade"
      @dismiss="filters.grades.splice(index, 1)"
    >
      {{ displayLongGrade(grade) }}
    </DismissibleBadge>

    <DismissibleBadge
      v-for="(type, index) in filters.types"
      :key="type"
      @dismiss="filters.types.splice(index, 1)"
    >
      {{ types[type] }}
    </DismissibleBadge>
  </FadeInGroup>
</template>

<script>
import { defineComponent } from 'vue'
import FadeInGroup from '@/components/transitions/FadeInGroup'
import DismissibleBadge from '@/components/DismissibleBadge'
import invoiceStatuses from '@/composition/invoiceStatuses'
import displaysGrades from '@/composition/displaysGrades'
import displaysDate from '@/composition/displaysDate'
import invoiceTypes from '@/composition/invoiceTypes'

export default defineComponent({
  components: {
    DismissibleBadge,
    FadeInGroup,
  },
  props: {
    filters: Object,
  },

  setup () {
    const { statuses } = invoiceStatuses()
    const { displayLongGrade } = displaysGrades()
    const { displayDate } = displaysDate()
    const types = invoiceTypes()

    return {
      statuses,
      displayLongGrade,
      displayDate,
      invoiceTypes,
      types,
    }
  }
})
</script>
