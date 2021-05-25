<template>
  <div>
    <Listbox v-model="locale">
      <div class="relative mt-1">
        <SonarListboxButton class="text-white bg-primary-800 dark:bg-primary-600 border-0">
          <span class="block truncate">{{ locales[locale] }}</span>

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
              v-for="(label, code) in locales"
              :key="code"
              :value="code"
              as="template"
            >
              <SonarListboxOption :active="active" :selected="selected">
                {{ label }}
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
    const locales = computed(() => props.value.locales)
    const locale = ref(props.value.locale)

    watch(locale, (newVal, oldVal) => {
      Inertia.post($route('locale'), {
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
