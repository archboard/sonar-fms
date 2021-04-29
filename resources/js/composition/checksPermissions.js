export default (permissions) => {
  const can = (...perms) => perms.every(perm => permissions[perm])
  const canAny = (...perms) => perms.some(perm => permissions[perm])

  return {
    can,
    canAny,
  }
}
