<template>
  <GenericObjectCombobox
    v-model="timezone"
    v-model:object-id="localValue"
    :options="options"
    search-attribute="name"
  />
</template>

<script setup>
import { inject, ref } from 'vue'
import reduce from 'just-reduce-object'
import GenericObjectCombobox from '@/components/forms/GenericObjectCombobox.vue'
import { useVModel } from '@vueuse/core'

const props = defineProps({
  modelValue: String,
})
const emit = defineEmits(['update:modelValue'])
const localValue = useVModel(props, 'modelValue', emit)
const $http = inject('$http')
const options = ref([])
const timezone = ref({})

$http.get('/timezones').then(({ data }) => {
  options.value = reduce(data, (carry, key, value) => {
    carry.push({
      id: key,
      name: value,
    })
    return carry
  }, [])

  if (localValue.value) {
    timezone.value = {
      id: localValue.value,
      name: data[localValue.value],
    }
  }
})
</script>
