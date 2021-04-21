<template>
  <div>
    <Listbox v-model="selectedSchool">
      <div class="relative mt-1">
        <SonarListboxButton class="bg-primary-800 dark:bg-primary-700">
          <span class="block truncate">{{ selectedSchool.name }}</span>

          <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
            <SelectorIcon class="w-5 h-5 text-gray-200" aria-hidden="true" />
          </span>
        </SonarListboxButton>

        <transition
          leave-active-class="transition duration-100 ease-in"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
        >
          <SonarListboxOptions>
            <ListboxOption
              v-slot="{ active, selected }"
              v-for="school in schools"
              :key="school.id"
              :value="school"
              as="template"
            >
              <SonarListboxOption :active="active" :selected="selected">
                {{ school.name }}
              </SonarListboxOption>
            </ListboxOption>
          </SonarListboxOptions>
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
  ListboxOption,
} from '@headlessui/vue'
import { CheckIcon, SelectorIcon } from '@heroicons/vue/solid'
import { usePage } from '@inertiajs/inertia-vue3'
import { Inertia } from '@inertiajs/inertia'
import SonarListboxButton from './forms/SonarListboxButton'
import SonarListboxOptions from './forms/SonarListboxOptions'
import SonarListboxOption from './forms/SonarListboxOption'

export default {
  components: {
    SonarListboxOption,
    SonarListboxOptions,
    SonarListboxButton,
    Listbox,
    ListboxLabel,
    ListboxOption,
    CheckIcon,
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
