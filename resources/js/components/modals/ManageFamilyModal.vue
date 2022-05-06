<template>
  <Modal
    @close="$emit('close')"
    :action-text="__('Finished')"
  >
    <ul class="mt-4">
      <template
        v-for="(student, index) in family.students"
        :key="student.uuid"
      >
        <li class="text-sm flex justify-between items-center">
          <div>
            {{ student.full_name }} ({{ student.grade_level_short_formatted }})
          </div>
          <div>
            <button
              @click.prevent="removeMember(student.uuid, index)"
              class="text-red-600 focus:outline-none rounded-full p-1"
            >
              <TrashIcon class="w-4 h-4" />
            </button>
          </div>
        </li>
      </template>
    </ul>

    <InputWrap :class="{ 'mt-4': family.students?.length > 0 }">
      <Label for="student_search">{{ __('Add a student') }}</Label>
      <StudentTypeahead v-model="student" :exclude="exclude" />
    </InputWrap>
  </Modal>
</template>

<script>
import { computed, defineComponent, inject, ref, watch } from 'vue'
import Modal from '@/components/Modal'
import managesFamilies from '@/composition/managesFamilies'
import { TrashIcon } from '@heroicons/vue/outline'
import InputWrap from '@/components/forms/InputWrap'
import Label from '@/components/forms/Label'
import StudentTypeahead from '@/components/forms/StudentTypeahead'

export default defineComponent({
  components: {
    StudentTypeahead,
    Label,
    InputWrap,
    Modal,
    TrashIcon,
  },
  emits: ['close'],
  props: {
    familyId: Number,
  },

  setup ({ familyId }) {
    const $http = inject('$http')
    const family = ref({})
    const student = ref({})
    const { fetchFamily } = managesFamilies()
    const removeMember = async (uuid, index) => {
      await $http.delete(`/families/${familyId}/students/${uuid}`)
      family.value.students.splice(index, 1)
    }
    const getMembers = async () => {
      family.value = await fetchFamily(familyId)
    }
    const exclude = computed(() => family.value?.students?.map(f => f.uuid) || [])
    getMembers()

    watch(student, async value => {
      if (value?.uuid) {
        await $http.post(`/families/${familyId}/students/${value.uuid}`)
        family.value.students.push(value)
        student.value = {}
      }
    })

    return {
      family,
      student,
      removeMember,
      exclude,
    }
  }
})
</script>
