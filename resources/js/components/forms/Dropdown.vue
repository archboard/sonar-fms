<template>
  <Menu as="div" class="relative inline-block text-left z-10">
    <div>
      <MenuButton class="focus:outline-none">
        <Button :size="size">
          <slot />
          <ChevronDownIcon class="-mr-1 ml-2" :class="iconSize" aria-hidden="true" />
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
import Button from '@/components/Button.vue'
import SonarMenuItems from '@/components/dropdown/SonarMenuItems.vue'
import SonarMenuItem from '@/components/forms/SonarMenuItem.vue'
import ScaleIn from '@/components/transitions/ScaleIn.vue'

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
    size: {
      type: String,
      default: 'base',
    }
  },

  setup ({ size }) {
    const iconSizes = {
      sm: 'h-4 w-4',
      base: 'h-5 w-5',
    }

    return {
      iconSize: iconSizes[size] || iconSizes.base,
    }
  }
}
</script>
