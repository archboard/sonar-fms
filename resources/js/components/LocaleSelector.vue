<template>
  <div class="relative">
    <select
      v-model="locale"
      class="block w-full pl-3 pr-10 py-2 text-sm text-white bg-primary-800 dark:bg-primary-600 bg-none border-0 focus:outline-none focus:ring focus:ring-fuchsia-500 rounded-md transition"
    >
      <option
        v-for="(label, code) in locales"
        :key="code"
        :value="code"
      >
        {{ label }}
      </option>
    </select>

    <span class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
      <SelectorIcon class="w-5 h-5 text-gray-200" aria-hidden="true" />
    </span>
  </div>
</template>

<script>
import { computed, inject, ref, watch } from 'vue'
import { SelectorIcon } from '@heroicons/vue/solid'
import { router, usePage } from '@inertiajs/vue3'

export default {
  components: {
    SelectorIcon,
  },

  setup () {
    const $route = inject('$route')
    const { props } = usePage()
    const locales = computed(() => props.locales)
    const locale = ref(props.locale)

    watch(locale, (newVal) => {
      router.post($route('locale'), {
        locale: newVal,
      }, {
        preserveScroll: true
      })
    })

    return {
      locale,
      locales,
    };
  },
};
</script>
