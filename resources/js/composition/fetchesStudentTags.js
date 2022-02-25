import { inject, ref } from 'vue'

export default () => {
  const $http = inject('$http')
  const allTags = ref([])
  const fetchAllTags = async () => {
    const { data } = await $http.get(`/tags/students`)
    allTags.value = data
  }

  return {
    allTags,
    fetchAllTags,
  }
}
