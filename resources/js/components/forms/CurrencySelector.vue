<template>
  <GenericObjectCombobox
    v-model="localObject"
    v-model:object-id="localValue"
    :options="currencies"
  />
</template>

<script setup>
import GenericObjectCombobox from '@/components/forms/GenericObjectCombobox.vue'
import { useVModel } from '@vueuse/core'
import { ref } from 'vue'

const props = defineProps({
  modelValue: {
    type: [String, Number],
  },
  currencies: Array,
})
const emit = defineEmits(['update:modelValue'])
const findCurrency = id => props.currencies.find(c => c.id === id)
const localObject = ref(findCurrency(props.modelValue) || {})
const localValue = useVModel(props, 'modelValue', emit)

// export default defineComponent({
//   emits: ['update:modelValue'],
//   props: {
//     modelValue: {
//       type: [String, Number],
//     },
//     currencies: Array,
//   },
//
//   setup (props, { emit }) {
//     const findCurrency = id => props.currencies.find(c => c.id === id)
//     const localValue = ref(findCurrency(props.modelValue)?.id || null)
//
//     watch(localValue, (newVal) => {
//       emit('update:modelValue', newVal)
//     })
//
//     return {
//       localValue,
//     }
//   }
// })
</script>
