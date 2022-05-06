import { inject } from 'vue'
import { useForm } from '@inertiajs/inertia-vue3'

export default (studentUuids = []) => {
  const $http = inject('$http')
  const familyForm = useForm({
    family_id: null,
    students: studentUuids,
    name: null,
    notes: null,
  })
  const fetchFamilies = async () => {
    try {
      const { data } = await $http.post('/search/families', {
        students: studentUuids
      })

      return data
    } catch (e) {
      return []
    }
  }
  const fetchFamily = async (familyId) => {
    try {
      const { data } = await $http.get(`/families/${familyId}`)
      return data
    } catch (e) {
      return {}
    }
  }
  const saveStudentsFamily = (callback) => {
    familyForm.post('/families', {
      preserve_scroll: true,
      onSuccess: () => {
        if (typeof callback === 'function') {
          callback()
        }
      }
    })
  }

  return {
    familyForm,
    fetchFamilies,
    saveStudentsFamily,
    fetchFamily,
  }
}
