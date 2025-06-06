<template>
  <slot v-if="localValue.isManual" />
  <ColumnSelector v-else v-model="localValue.column" :headers="headers" :id="id" :required="required" />
  <div>
    <button @click.prevent="localValue.isManual = !localValue.isManual" class="text-sm text-primary-500 hover:text-primary-400 focus:outline-none focus:underline translate">
      <span v-if="localValue.isManual">{{ __('Map to column') }}</span>
      <span v-else>{{ __('Set custom value') }}</span>
    </button>
  </div>
  <slot name="after" />
</template>

<script>
import { defineComponent, reactive, watch } from 'vue'
import ColumnSelector from '@/components/forms/ColumnSelector.vue'

export default defineComponent({
  components: {
    ColumnSelector,
  },
  props: {
    modelValue: Object,
    headers: Array,
    id: String,
    required: {
      type: Boolean,
      default: false,
    }
  },
  emits: ['update:modelValue', 'manual'],

  setup (props, { emit }) {
    const localValue = reactive({
      ...props.modelValue
    })

    watch(() => ({ ...props.modelValue }), state => {
      localValue.id = state.id
      localValue.column = state.column
      localValue.isManual = state.isManual
      localValue.value = state.value
    })

    watch(
      () => ({ ...localValue }),
      (state) => {
        emit('update:modelValue', state)

        if (state.isManual) {
          emit('manual')
        }
      }
    )

    return {
      localValue,
    }
  }
})
</script>
