import get from 'lodash/get'

export default (permissions) => {
  const can = (...perms) => perms.every(perm => get(permissions, perm))
  const canAny = (...perms) => perms.some(perm => get(permissions, perm))

  return {
    can,
    canAny,
  }
}
