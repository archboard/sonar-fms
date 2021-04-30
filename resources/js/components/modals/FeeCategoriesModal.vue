<template>
  <Modal
    @action="() => saveCategory(categoryForm)"
    @close="$emit('close')"
    :auto-close="false"
    :headline="__('Fee categories')"
    :action-loading="categoryForm.processing"
  >
    <ul>
      <li
        v-for="cat in categories"
        :key="cat.id"
        class="flex items-center space-x-2"
      >
        <span>
          {{ cat.name }}
        </span>
        <Link is="button" class="text-sm" @click.prevent="editCat(cat)">
          {{ __('Edit') }}
        </Link>
      </li>
    </ul>

    <form @submit.prevent="saveCategory(categoryForm)">
      <ModalHeadline v-if="categoryForm.id" class="mb-4 mt-6">{{ __('Update category') }}</ModalHeadline>
      <ModalHeadline v-else class="mb-4 mt-6">{{ __('Add a new category') }}</ModalHeadline>
      <Fieldset>
        <InputWrap :error="categoryForm.errors.name">
          <Label for="new-cat-name" :required="true">{{ __('Name') }}</Label>
          <Input v-model="categoryForm.name" id="new-cat-name" />
        </InputWrap>
      </Fieldset>
    </form>
  </Modal>
</template>

<script>
import { defineComponent, ref } from 'vue'
import Modal from '../Modal'
import { useForm } from '@inertiajs/inertia-vue3'
import handlesFeeCategories from '../../composition/handlesFeeCategories'
import Fieldset from '../forms/Fieldset'
import Label from '../forms/Label'
import InputWrap from '../forms/InputWrap'
import Input from '../forms/Input'
import ModalHeadline from './ModalHeadline'
import Link from '../Link'

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
    const categoryForm = useForm({
      id: null,
      name: ''
    })
    const { categories, saveCategory } = handlesFeeCategories()
    const editCat = cat => {
      categoryForm.id = cat.id
      categoryForm.name = cat.name
    }

    return {
      categories,
      categoryForm,
      saveCategory,
      editCat,
    }
  }
})
</script>
