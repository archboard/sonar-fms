<template>
  <Slideout
    @close="$emit('close')"
    @action="savePermissions"
    :auto-close="true"
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
      <div
        v-for="permission in permissions"
        :key="permission.model"
      >
        {{ permission.label }}
      </div>
    </Fieldset>
    <pre>{{ permissions }}</pre>
  </Slideout>
</template>

<script>
import { computed, inject, onMounted, ref } from 'vue'
import { usePage } from '@inertiajs/inertia-vue3'
import Fieldset from '../forms/Fieldset'
import Slideout from '../Slideout'
import CardHeader from '../CardHeader'
import HelpText from '../HelpText'

export default {
  components: {
    HelpText,
    CardHeader,
    Slideout,
    Fieldset,
  },
  props: {
    user: Object,
  },
  emits: ['close'],

  setup (props) {
    const $route = inject('$route')
    const $http = inject('$http')

    const page = usePage()
    const school = computed(() => page.props.value.school)
    const permissions = ref([])

    const savePermissions = (close) => {}

    onMounted(() => {
      $http.get($route('users.permissions', props.user)).then(({ data }) => {
        permissions.value = data
      })
    })

    return {
      savePermissions,
      school,
      permissions,
    }
  },
}
</script>
