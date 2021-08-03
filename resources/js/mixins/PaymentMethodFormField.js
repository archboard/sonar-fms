export default {
  props: {
    modelValue: Object,
  },
  emits: ['update:modelValue'],

  computed: {
    localValue: {
      get: function () {
        return this.modelValue
      },
      set: function (state) {
        this.$emit('update:modelValue', state)
      }
    }
  }
}
