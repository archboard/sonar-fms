<template>
  <Menu as="div" class="relative inline-block text-left">
    <MenuButton as="template" v-slot="{ open }"
    >
      <button
        type="button"
        class="flex w-5 h-5 items-center justify-center rounded-full focus:outline-none focus:ring focus:ring-gray-200 dark:focus:ring-gray-400"
        :class="{
          'ring ring-gray-200 dark:ring-gray-400': open
        }"
      >
        <DotsVerticalIcon class="h-4 w-4" />
      </button>
    </MenuButton>

    <transition
      enter-active-class="transition duration-100 ease-out"
      enter-from-class="transform scale-95 opacity-0"
      enter-to-class="transform scale-100 opacity-100"
      leave-active-class="transition duration-75 ease-in"
      leave-from-class="transform scale-100 opacity-100"
      leave-to-class="transform scale-95 opacity-0"
    >
      <MenuItems
        class="absolute z-10 right-0 w-56 mt-2 origin-top-right bg-white dark:bg-gray-600 divide-y divide-gray-100 dark:divide-gray-400 rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
      >
        <slot/>
      </MenuItems>
    </transition>
  </Menu>
</template>

<script>
import { Menu, MenuButton, MenuItems, MenuItem } from "@headlessui/vue"
import { DotsVerticalIcon, PencilIcon } from '@heroicons/vue/outline'

export default {
  components: {
    Menu,
    MenuButton,
    MenuItems,
    MenuItem,
    PencilIcon,
    DotsVerticalIcon,
  },
};
</script>
