<template>
  <Listbox v-model="localValue">
    <ListboxButton class="flex" ref="trigger">
      <slot />
    </ListboxButton>


      <div ref="container" class="w-56 z-10">
        <ScaleIn>
          <ListboxOptions class="absolute w-full p-1 mt-1 overflow-auto space-y-1 rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 focus:outline-none">
            <ListboxOption
              as="template"
              v-slot="{ active, selected }"
              v-for="(colorClass, colorKey) in colors"
              :key="colorKey"
              :value="colorKey"
            >
              <li
                :class="[
                  (active || selected) ? classes.active : classes.inactive,
                  classes.always,
                  'flex space-x-2',
                ]"
              >
                <span class="h-3 w-3 rounded-full" :class="colorClass" aria-hidden="true"></span>
                <span>{{ capitalize(colorKey) }}</span>
              </li>
            </ListboxOption>
          </ListboxOptions>
        </ScaleIn>
      </div>
  </Listbox>
</template>

<script>
import { computed, defineComponent, ref } from 'vue'
import tagColorKey from '@/composition/tagColorKey.js'
import { Listbox, ListboxButton, ListboxOption, ListboxOptions } from '@headlessui/vue'
import capitalize from 'just-capitalize'
import DropIn from '@/components/transitions/DropIn.vue'
import menuItemClasses from '@/composition/menuItemClasses.js'
import usePopper from '@/composition/usePopper.js'
import ScaleIn from '@/components/transitions/ScaleIn.vue'

export default defineComponent({
  components: {
    ScaleIn,
    DropIn,
    ListboxOption,
    ListboxOptions,
    ListboxButton,
    Listbox,
  },
  props: {
    modelValue: String,
  },
  emits: ['update:modelValue'],

  setup (props, { emit }) {
    const localValue = computed({
      get: () => props.modelValue,
      set: value => emit('update:modelValue', value)
    })
    const colors = tagColorKey()
    const classes = menuItemClasses()
    const { trigger, container, update } = usePopper({
      placement: 'bottom-end',
      modifiers: [{ name: 'offset', options: { offset: [0, 8] } }],
    })

    return {
      localValue,
      colors,
      capitalize,
      classes,
      trigger,
      container,
    }
  }
})
</script>
