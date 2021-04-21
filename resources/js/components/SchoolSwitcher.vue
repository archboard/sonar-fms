<template>
  <div>
    <Listbox v-model="selectedSchool">
      <div class="relative mt-1">
        <ListboxButton
          class="relative w-full py-2 pl-3 pr-10 text-left bg-primary-800 dark:bg-primary-700 text-white rounded-lg cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-opacity-75 focus-visible:ring-white focus-visible:ring-offset-fuchsia-300 focus-visible:ring-offset-2 focus-visible:border-primary-500 sm:text-sm"
        >
          <span class="block truncate">{{ selectedSchool.name }}</span>
          <span
            class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none"
          >
            <SelectorIcon class="w-5 h-5 text-gray-200" aria-hidden="true" />
          </span>
        </ListboxButton>

        <transition
          leave-active-class="transition duration-100 ease-in"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
        >
          <ListboxOptions
            class="absolute w-full py-1 mt-1 overflow-auto text-base bg-white rounded-md shadow-lg max-h-64 ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm"
          >
            <ListboxOption
              v-slot="{ active, selected }"
              v-for="school in schools"
              :key="school.id"
              :value="school"
              as="template"
            >
              <li
                :class="[
                  active ? 'text-fuchsia-900 bg-fuchsia-100' : 'text-gray-900',
                  'cursor-pointer select-none relative py-2 pl-10 pr-4',
                ]"
              >
                <span
                  :class="[
                    selected ? 'font-medium' : 'font-normal',
                    'block truncate',
                  ]"
                  >{{ school.name }}</span
                >
                <span
                  v-if="selected"
                  class="absolute inset-y-0 left-0 flex items-center pl-3 text-fuchsia-600"
                >
                  <CheckIcon class="w-5 h-5" aria-hidden="true" />
                </span>
              </li>
            </ListboxOption>
          </ListboxOptions>
        </transition>
      </div>
    </Listbox>
  </div>
</template>

<script>
import { computed, inject, ref, watch } from 'vue'
import {
  Listbox,
  ListboxLabel,
  ListboxButton,
  ListboxOptions,
  ListboxOption,
} from '@headlessui/vue'
import { CheckIcon, SelectorIcon } from '@heroicons/vue/solid'
import { usePage } from '@inertiajs/inertia-vue3'
import { Inertia } from '@inertiajs/inertia'

export default {
  components: {
    Listbox,
    ListboxLabel,
    ListboxButton,
    ListboxOptions,
    ListboxOption,
    CheckIcon,
    SelectorIcon,
  },

  setup () {
    const $route = inject('$route')
    const { props } = usePage()
    const schools = computed(() => props.value.user.schools)
    const selectedSchool = ref(props.value.user.schools.find(s => s.id === props.value.user.school_id))
    watch(selectedSchool, () => {
      Inertia.put($route('schools.change'), {
        school_id: selectedSchool.value.id,
      }, {
        preserveScroll: true,
      })
    })

    return {
      schools,
      selectedSchool,
    };
  },
};
</script>
