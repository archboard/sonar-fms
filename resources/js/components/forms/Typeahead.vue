<template>
  <Input
    :model-value="displayValue"
    :placeholder="placeholder"
  />

  <div v-if="showOptions" class="relative">
    <ul class="absolute inset-0">
      <li
        v-for="(item, index) in items"
        :key="item.__id"
        @mouseover="activeIndex = index"
        @click="$emit('selected', item)"
      >
        <slot
          name="item"
          :item="item"
          :active="index === activeIndex"
        />
      </li>
    </ul>
  </div>
</template>

<script>
import { computed, defineComponent, onMounted, onUnmounted, ref } from 'vue'
import Input from '@/components/forms/Input'
import { nanoid } from 'nanoid'

export default defineComponent({
  components: {
    Input,
  },
  emits: ['selected'],
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
    const showOptions = ref(false)
    const activeIndex = ref(0)
    const mappedItems = computed(() => {
      return props.items.map(item => {
        item.__id = nanoid()

        return item
      })
    })
    const listener = (e) => {
      if (e.key === 'Enter') {
        e.stopPropagation()
        emit('selected', mappedItems.value[activeIndex.value])
      }
    }

    onMounted(() => {
      document.addEventListener('keydown', listener)
    })

    onUnmounted(() => {
      document.removeEventListener('keydown', listener)
    })

    return {
      activeIndex,
      mappedItems,
      showOptions,
    }
  }
})
</script>
