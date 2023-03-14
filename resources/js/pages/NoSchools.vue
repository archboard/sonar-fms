<template>
  <Layout>
    <p>{{ __("You don't have any schools associated with your account. Please select your school from the list below:") }}</p>
    <form @submit.prevent="save">
      <div class="space-y-2 mt-4">
        <InputWrap
          v-for="school in schools"
          :key="school.id"
        >
          <Label class="flex items-center">
            <CheckboxText>
              <Radio name="schools" v-model:checked="form.school_id" :value="school.id" class="mr-2" />
              <span
              >
                {{ school.name }}
              </span>
            </CheckboxText>
          </Label>
        </InputWrap>
      </div>

      <CardAction negative-margin>
        <Button type="submit" :loading="form.processing" />
      </CardAction>
    </form>
  </Layout>
</template>

<script setup>
import Layout from '@/layouts/Guest.vue'
import InputWrap from '@/components/forms/InputWrap.vue'
import CheckboxText from '@/components/forms/CheckboxText.vue'
import Radio from '@/components/forms/Radio.vue'
import { useForm } from '@inertiajs/vue3'
import Label from '@/components/forms/Label.vue'
import CardAction from '@/components/CardAction.vue'
import Button from '@/components/Button.vue'

const props = defineProps({
  user: Object,
  schools: Array,
})
const form = useForm({
  school_id: null,
})
const save = () => {
  form.put(`/select-school`, {
    preserveScroll: true,
  })
}
</script>
