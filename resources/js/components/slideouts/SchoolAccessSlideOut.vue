<template>
  <Slideout
    @close="$emit('close')"
    @action="save"
    :auto-close="false"
    :processing="saving"
  >
    <template v-slot:header>
      <div class="space-y-1">
        <CardHeader>
          {{ __('School access for :name', { name: user.full_name }) }}
        </CardHeader>
        <HelpText>
          {{ __('Adjust the schools to which :name has access.', { name: user.first_name }) }}
        </HelpText>
      </div>
    </template>

    <Alert class="mb-6">
      {{ __('If a user has access to the given school in :sis, they will be given access when they log in.', { sis: tenant.sis }) }}
    </Alert>

    <Loader v-if="schools.length === 0" />

    <Fieldset>
      <InputWrap
        v-for="school in schools"
        :key="school.id"
      >
        <Label class="flex items-center">
          <Checkbox name="schools" v-model:checked="school.has_access" :value="school.id" class="mr-2" />
          <CheckboxText class="space-x-3">
            <span>
              {{ school.name }}
            </span>

            <SolidBadge v-if="school.is_current" color="primary">
              {{ __('Current school' )}}
            </SolidBadge>
            <SolidBadge v-if="!school.active" color="yellow">
              {{ __('Inactive' )}}
            </SolidBadge>
          </CheckboxText>
        </Label>
      </InputWrap>
    </Fieldset>
  </Slideout>
</template>

<script>
import { inject, ref } from 'vue'
import Fieldset from '@/components/forms/Fieldset'
import Slideout from '@/components/Slideout'
import CardHeader from '@/components/CardHeader'
import HelpText from '@/components/HelpText'
import InputWrap from '@/components/forms/InputWrap'
import Checkbox from '@/components/forms/Checkbox'
import Label from '@/components/forms/Label'
import CheckboxText from '@/components/forms/CheckboxText'
import CheckboxWrapper from '@/components/forms/CheckboxWrapper'
import useSchool from '@/composition/useSchool'
import Loader from '@/components/Loader'
import SolidBadge from '@/components/SolidBadge'
import Alert from '@/components/Alert'
import useProp from '@/composition/useProp'

export default {
  components: {
    Alert,
    SolidBadge,
    Loader,
    CheckboxWrapper,
    CheckboxText,
    Checkbox,
    InputWrap,
    HelpText,
    CardHeader,
    Slideout,
    Fieldset,
    Label,
  },
  props: {
    user: Object,
  },
  emits: ['close'],

  setup (props) {
    const $route = inject('$route')
    const $http = inject('$http')

    const { school } = useSchool()
    const tenant = useProp('tenant')
    const schools = ref([])
    const saving = ref(false)

    const getSchools = async () => {
      try {
        const { data } = await $http.get($route('users.schools', props.user))
        schools.value = data.map(s => {
          s.is_current = school.value.id === s.id

          return s
        })
      } catch (err) { console.log(err) }
    }
    const save = async (close) => {
      saving.value = true

      try {
        await $http.put($route('users.schools', props.user), {
          schools: schools.value
        })
        close()
      } catch (err) { }

      saving.value = false
    }

    getSchools()

    return {
      save,
      school,
      schools,
      saving,
      tenant,
    }
  },
}
</script>
