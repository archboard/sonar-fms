<template>
  <input
    type="checkbox"
    :value="value"
    v-model="proxyChecked"
    @change="$emit('change')"
   class="rounded cursor-pointer border-gray-300 text-primary-600 dark:text-primary-500 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50"
  >
</template>

<script>
export default {
  emits: [
    'update:checked',
    'change'
  ],

  props: {
    checked: {
      type: [Array, Boolean],
      default: false,
    },
    value: {
      default: null,
    },
  },

  computed: {
    proxyChecked: {
      get () {
        return this.checked;
      },

      set (val) {
        this.$emit("update:checked", val);
      },
    },
  },
}
</script>
