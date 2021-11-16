<template>
  <div class="relative">
    <Input
      :model-value="displayValue"
      :placeholder="placeholder"
      v-bind="$attrs"
      @focus="onFocus"
      @blur="onBlur"
      ref="input"
    />

    <DropIn>
      <div v-if="showOptions" class="absolute z-20 w-full top-full">
        <div class="mt-2 bg-gray-100 dark:bg-gray-700 space-y-4 rounded-lg shadow-lg p-3 overflow-hidden">
          <ul class="space-y-1">
            <li
              v-for="(item, index) in items"
              :key="item.__id"
              @mouseover="activeIndex = index"
              @click="select"
              class="cursor-pointer"
            >
              <slot
                name="item"
                :item="item"
                :active="index === activeIndex"
              />
            </li>
          </ul>
        </div>
      </div>
    </DropIn>
  </div>
</template>

<script>
import { computed, defineComponent, onMounted, onUnmounted, ref } from 'vue'
import Input from '@/components/forms/Input'
import { nanoid } from 'nanoid'
import DropIn from '@/components/transitions/DropIn'

export default defineComponent({
  inheritAttrs: false,
  components: {
    DropIn,
    Input,
  },
  emits: ['search', 'selected'],
  props: {
    displayValue: {
      type: String,
      default: '',
    },
    placeholder: {
      type: String,
      default: '',
    },
    items: {
      type: Array,
      default: () => ([])
    },
  },

  setup (props, { emit }) {
    const input = ref(null)
    const focusing = ref(false)
    const selectionMade = ref(false)
    const showOptions = computed(() => {
      return focusing.value &&
        !selectionMade.value &&
        mappedItems.value.length > 0
    })
    const activeIndex = ref(0)
    const mappedItems = computed(() => {
      return props.items.map(item => {
        item.__id = nanoid()

        return item
      })
    })
    const progressIndex = (amount) => {
      const nextIndex = activeIndex.value + amount
      const lastIndex = mappedItems.value.length - 1

      if (nextIndex < 0) {
        activeIndex.value = lastIndex
        return
      }

      if (nextIndex > lastIndex) {
        activeIndex.value = 0
        return
      }

      activeIndex.value = nextIndex
    }
    const listener = (e) => {
      switch (e.code) {
        case 'Enter':
          e.preventDefault()
          e.stopPropagation()
          select()
          break
        case 'ArrowUp':
          progressIndex(-1)
          break
        case 'ArrowDown':
          progressIndex(1)
          break
      }
    }
    const select = () => {
      emit('selected', mappedItems.value[activeIndex.value])
      selectionMade.value = true
    }
    const onFocus = () => {
      focusing.value = true
      selectionMade.value = false
    }
    const onBlur = () => {
      focusing.value = false
    }

    onMounted(() => {
      input.value?.$el.addEventListener('keydown', listener)
    })

    onUnmounted(() => {
      input.value?.$el.removeEventListener('keydown', listener)
    })

    return {
      focusing,
      activeIndex,
      mappedItems,
      showOptions,
      select,
      input,
      onFocus,
      onBlur,
    }
  }
})
</script>
