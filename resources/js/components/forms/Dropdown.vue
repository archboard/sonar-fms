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

      <ScaleIn>
        <SonarMenuItems>
          <slot name="dropdown">
            <div class="p-1">
              <SonarMenuItem
                v-for="item in menuItems"
                is="inertia-link"
                :href="item.route"
              >
                {{ item.label }}
              </SonarMenuItem>
            </div>
          </slot>
        </SonarMenuItems>
      </ScaleIn>
  </Menu>
</template>

<script>
import { Menu, MenuButton } from '@headlessui/vue'
import { ChevronDownIcon } from '@heroicons/vue/solid'
import Button from '../Button'
import SonarMenuItems from '@/components/dropdown/SonarMenuItems'
import SonarMenuItem from '@/components/forms/SonarMenuItem'
import ScaleIn from '@/components/transitions/ScaleIn'

export default {
  components: {
    ScaleIn,
    SonarMenuItem,
    SonarMenuItems,
    Button,
    Menu,
    MenuButton,
    ChevronDownIcon,
  },

  props: {
    menuItems: Array,
  }
}
</script>
