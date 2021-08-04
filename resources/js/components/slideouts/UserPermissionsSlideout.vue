<template>
  <Slideout
    @close="$emit('close')"
    @action="savePermissions"
    :auto-close="false"
    :processing="saving"
  >
    <template v-slot:header>
      <div class="space-y-1">
        <CardHeader>
          {{ __('Permissions for :name', { name: user.full_name }) }}
        </CardHeader>
        <HelpText>
          {{ __('Adjust the permissions that :name has for :school.', { name: user.first_name, school: school.name }) }}
        </HelpText>
      </div>
    </template>

    <Fieldset>
      <InputWrap v-if="currentUser.manages_tenancy">
        <CheckboxWrapper>
          <Checkbox v-model:checked="managesTenancy" name="manages_tenancy" />
          <CheckboxText>{{ __('Manages tenancy') }}</CheckboxText>
        </CheckboxWrapper>
        <HelpText>{{ __('When checked, this user can manage your entire tenancy. This means the user has full admin access to the entire application. If your tenancy has multiple schools, it does not automatically add them to all of the schools. You will still need to add them to schools manually.') }}</HelpText>
      </InputWrap>

      <InputWrap v-if="(currentUser.manages_tenancy || authUserManagesSchool) && !managesTenancy">
        <CheckboxWrapper>
          <Checkbox v-model:checked="managesSchool" />
          <CheckboxText>{{ __('Manages school') }}</CheckboxText>
        </CheckboxWrapper>
        <HelpText>{{ __('When checked, this user has full permission for :school, but not special tenancy permissions.') }}</HelpText>
      </InputWrap>
    </Fieldset>

    <div class="space-y-4 divide-y mt-8">
      <div
        v-for="permission in permissions.models"
        :key="permission.model"
        class="pt-3"
      >
        <h4>{{ permission.label }}</h4>

        <div class="grid grid-cols-4 gap-4 mt-4">
          <div
            v-for="item in permission.permissions"
            :key="item.permission"
          >
            <CheckboxWrapper
            >
              <Checkbox v-model:checked="item.can" />
              <CheckboxText>{{ item.label }}</CheckboxText>
            </CheckboxWrapper>
          </div>
        </div>
      </div>
    </div>
  </Slideout>
</template>

<script>
import { computed, inject, nextTick, onMounted, ref, watch } from 'vue'
import { usePage } from '@inertiajs/inertia-vue3'
import Fieldset from '../forms/Fieldset'
import Slideout from '../Slideout'
import CardHeader from '../CardHeader'
import HelpText from '../HelpText'
import InputWrap from '../forms/InputWrap'
import Checkbox from '../forms/Checkbox'
import Label from '../forms/Label'
import CheckboxText from '../forms/CheckboxText'
import CheckboxWrapper from '../forms/CheckboxWrapper'
import useSchool from '@/composition/useSchool'

export default {
  components: {
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
    authUserManagesSchool: Boolean,
  },
  emits: ['close'],

  setup (props) {
    let firstFetch = true
    const $route = inject('$route')
    const $http = inject('$http')

    const page = usePage()
    const { school } = useSchool()
    const permissions = ref([])
    const currentUser = computed(() => page.props.value.user)
    const managesTenancy = ref(props.user.manages_tenancy)
    const managesSchool = ref(false)
    const saving = ref(false)

    const getPermissions = () => {
      $http.get($route('users.permissions', props.user)).then(({ data }) => {
        permissions.value = data
        managesTenancy.value = data.manages_tenancy
        managesSchool.value = data.manages_school
        nextTick(() => {
          firstFetch = false
        })
      })
    }
    const savePermissions = (close) => {
      saving.value = true

      $http.put($route('users.permissions', props.user), permissions.value).then(() => {
        saving.value = false
        close()
      })
    }

    watch(managesTenancy, (newVal) => {
      props.user.manages_tenancy = newVal
      $http.put($route('users.tenancy_manager', props.user)).then(getPermissions)
    })

    watch(managesSchool, () => {
      // Don't change after first fetch, since it's setting the initial value
      if (
        !firstFetch &&
        (currentUser.value.manages_tenancy || props.authUserManagesSchool)
      ) {
        $http.put($route('users.school-admin', props.user)).then(getPermissions)
      }
    })

    getPermissions()

    return {
      savePermissions,
      school,
      permissions,
      currentUser,
      managesTenancy,
      managesSchool,
      saving,
    }
  },
}
</script>
