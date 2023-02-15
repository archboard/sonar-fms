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
import { computed, inject, nextTick, ref, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import Fieldset from '@/components/forms/Fieldset.vue'
import Slideout from '@/components/Slideout.vue'
import CardHeader from '@/components/CardHeader.vue'
import HelpText from '@/components/HelpText.vue'
import InputWrap from '@/components/forms/InputWrap.vue'
import Checkbox from '@/components/forms/Checkbox.vue'
import Label from '@/components/forms/Label.vue'
import CheckboxText from '@/components/forms/CheckboxText.vue'
import CheckboxWrapper from '@/components/forms/CheckboxWrapper.vue'
import useSchool from '@/composition/useSchool.js'
import usesUser from '@/composition/usesUser.js'

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
    const $http = inject('$http')

    const { school } = useSchool()
    const permissions = ref([])
    const currentUser = usesUser().user
    const managesTenancy = ref(props.user.manages_tenancy)
    const managesSchool = ref(false)
    const saving = ref(false)

    const getPermissions = () => {
      $http.get(`/users/${props.user.uuid}/permissions`).then(({ data }) => {
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

      $http.put(`/users/${props.user.uuid}/permissions`, permissions.value).then(() => {
        saving.value = false
        close()
      })
    }

    watch(managesTenancy, (newVal) => {
      props.user.manages_tenancy = newVal
      $http.put(`/settings/users/${props.user.uuid}/manager`).then(getPermissions)
    })

    watch(managesSchool, () => {
      // Don't change after first fetch, since it's setting the initial value
      if (
        !firstFetch &&
        (currentUser.value.manages_tenancy || props.authUserManagesSchool)
      ) {
        $http.put(`/users/${props.user.uuid}/school-admin`).then(getPermissions)
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
