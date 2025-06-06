<template>
  <Modal
    @action="() => saveDepartment(deptForm)"
    @close="$emit('close')"
    :auto-close="false"
    :headline="__('Departments')"
    :action-loading="deptForm.processing"
  >
    <ul>
      <li
        v-for="dept in departments"
        :key="dept.id"
        class="flex items-center space-x-2"
      >
        <span>
          {{ dept.name }}
        </span>
        <Link is="button" class="text-sm" @click.prevent="editDept(dept)">
          {{ __('Edit') }}
        </Link>
      </li>
    </ul>

    <form @submit.prevent="saveDepartment(deptForm)">
      <ModalHeadline v-if="deptForm.id" class="mb-4 mt-6">{{ __('Update department') }}</ModalHeadline>
      <ModalHeadline v-else class="mb-4 mt-6">{{ __('Add a new department') }}</ModalHeadline>
      <Fieldset>
        <InputWrap :error="deptForm.errors.name">
          <Label for="new-dept-name" :required="true">{{ __('Name') }}</Label>
          <Input v-model="deptForm.name" id="new-dept-name" />
        </InputWrap>
      </Fieldset>
    </form>
  </Modal>
</template>

<script>
import { defineComponent } from 'vue'
import Modal from '@/components/Modal.vue'
import { useForm } from '@inertiajs/vue3'
import handlesDepartments from '@/composition/handlesDepartments'
import Fieldset from '@/components/forms/Fieldset.vue'
import Label from '@/components/forms/Label.vue'
import InputWrap from '@/components/forms/InputWrap.vue'
import Input from '@/components/forms/Input.vue'
import ModalHeadline from '@/components/modals/ModalHeadline.vue'
import Link from '@/components/Link.vue'

export default defineComponent({
  components: {
    ModalHeadline,
    Input,
    InputWrap,
    Fieldset,
    Modal,
    Label,
    Link,
  },

  setup () {
    const deptForm = useForm({
      id: null,
      name: ''
    })
    const { departments, saveDepartment } = handlesDepartments()
    const editDept = dept => {
      deptForm.id = dept.id
      deptForm.name = dept.name
    }

    return {
      departments,
      deptForm,
      saveDepartment,
      editDept,
    }
  }
})
</script>
