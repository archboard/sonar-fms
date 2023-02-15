<template>
  <Authenticated>
    <MyStudents :students="students" />
  </Authenticated>
</template>

<script>
import { defineComponent, ref } from 'vue'
import Authenticated from '@/layouts/Authenticated.vue'
import MyStudents from '@/components/tables/MyStudents.vue'

export default defineComponent({
  components: {
    MyStudents,
    Authenticated,
  },
  props: {
    students: Array,
  },

  setup () {
  }
})
</script>
