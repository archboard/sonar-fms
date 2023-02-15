import get from 'lodash/get'
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

export default (permissions = {}) => {
  const localPermissions = computed(() => usePage().props.permissions || permissions)
  const can = (...perms) => perms.every(perm => get(localPermissions.value, perm))
  const canAny = (...perms) => perms.some(perm => get(localPermissions.value, perm))

  return {
    can,
    canAny,
  }
}
