<template>
  <div class="relative">
    <select
      v-model="selectedSchool"
      class="block w-full pl-3 pr-10 py-2 text-sm text-white bg-primary-800 dark:bg-primary-700 bg-none border-0 focus:outline-none focus:ring focus:ring-fuchsia-500 rounded-md transition"
    >
      <option
        v-for="school in schools"
        :key="school.id"
        :value="school"
      >
        {{ school.name }}
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
import { usePage } from '@inertiajs/inertia-vue3'
import { Inertia } from '@inertiajs/inertia'

export default {
  components: {
    SelectorIcon,
  },

  setup () {
    const $route = inject('$route')
    const { props } = usePage()
    const schools = computed(() => props.value.user.schools)
    const selectedSchool = ref(props.value.user.schools.find(s => s.id === props.value.user.school_id))
    watch(selectedSchool, (newVal, oldVal) => {
      Inertia.put($route('schools.change'), {
        school_id: newVal.id,
      }, {
        preserveScroll: true,
        onSuccess (page) {
          if (page.props.flash.error) {
            selectedSchool.value = oldVal
          }
        },
      })
    })

    return {
      schools,
      selectedSchool,
    };
  },
};
</script>
