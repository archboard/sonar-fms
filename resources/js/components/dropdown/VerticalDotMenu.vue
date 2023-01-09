<template>
  <Menu
    as="div"
    class="relative inline-block text-left"
  >
    <MenuButton
      as="template"
      v-slot="{ open }"
      ref="trigger"
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

    <teleport to="#tooltip-container">
      <div ref="container" class="w-56 z-10">
        <transition
          enter-active-class="transition duration-100 ease-out"
          enter-from-class="transform scale-95 opacity-0"
          enter-to-class="transform scale-100 opacity-100"
          leave-active-class="transition duration-75 ease-in"
          leave-from-class="transform scale-100 opacity-100"
          leave-to-class="transform scale-95 opacity-0"
        >
          <MenuItems
            class="w-full bg-white dark:bg-gray-600 divide-y divide-gray-100 dark:divide-gray-400 rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
            ref="menuItemsRef"
          >
            <slot/>
          </MenuItems>
        </transition>
      </div>
    </teleport>
  </Menu>
</template>

<script>
import { defineComponent, ref, watchEffect } from 'vue'
import { Menu, MenuButton, MenuItems, MenuItem } from "@headlessui/vue"
import { DotsVerticalIcon, PencilIcon } from '@heroicons/vue/outline'
import usePopper from '@/composition/usePopper.js'

export default defineComponent({
  components: {
    Menu,
    MenuButton,
    MenuItems,
    MenuItem,
    PencilIcon,
    DotsVerticalIcon,
  },

  setup () {
    const { trigger, container, update } = usePopper({
      placement: 'bottom-end',
      strategy: 'fixed',
      modifiers: [{ name: 'offset', options: { offset: [0, 8] } }],
    })
    const menuItemsRef = ref(null)

    // When the fonts are loaded, the popper instance needs to be updated
    // otherwise it shows up off the page
    watchEffect(() => {
      if (menuItemsRef.value && menuItemsRef.value.visible) {
        update()
      }
    })

    return {
      trigger,
      container,
      menuItemsRef,
    }
  },
})
</script>
