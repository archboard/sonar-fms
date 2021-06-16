<template>
  <Menu as="div" class="relative inline-block text-left z-10">
    <div>
      <MenuButton class="focus:outline-none">
        <Button>
          <slot />
          <ChevronDownIcon class="-mr-1 ml-2 h-5 w-5" aria-hidden="true" />
        </Button>
      </MenuButton>
    </div>

    <transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <MenuItems class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 focus:outline-none">
        <div class="py-1">
          <MenuItem
            v-slot="{ active }"
            v-for="item in menuItems"
          >
            <component :is="item.component || 'inertia-link'" :href="item.route" :class="[active ? 'bg-gray-100 text-gray-900 dark:bg-gray-800 dark:text-white' : 'text-gray-700 dark:text-gray-100', 'block px-4 py-2 text-sm']">
              {{ item.label }}
            </component>
          </MenuItem>
        </div>
      </MenuItems>
    </transition>
  </Menu>
</template>

<script>
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { ChevronDownIcon } from '@heroicons/vue/solid'
import Button from '../Button'

export default {
  components: {
    Button,
    Menu,
    MenuButton,
    MenuItem,
    MenuItems,
    ChevronDownIcon,
  },

  props: {
    menuItems: Array,
  }
}
</script>
