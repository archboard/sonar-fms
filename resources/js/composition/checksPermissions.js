import get from 'lodash/get'
import { usePage } from '@inertiajs/inertia-vue3'
import { computed } from 'vue'

export default (permissions = {}) => {
  const page = usePage()
  const localPermissions = computed(
    () => page.props.value.permissions || permissions
  )
  const can = (...perms) => perms.every(perm => get(localPermissions.value, perm))
  const canAny = (...perms) => perms.some(perm => get(localPermissions.value, perm))

  return {
    can,
    canAny,
  }
}
