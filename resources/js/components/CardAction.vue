<template>
  <div
    class="bg-gray-50 dark:bg-gray-700"
    :class="{
      '-mx-4 -mb-5 mt-6 sm:-mx-6': negativeMargin
    }"
  >
    <CardPadding>
      <slot/>
    </CardPadding>
  </div>
</template>

<script>
import { defineComponent } from 'vue'
import CardPadding from './CardPadding'

export default defineComponent({
  components: {
    CardPadding
  },

  props: {
    negativeMargin: {
      type: Boolean,
      default: false
    }
  }
})
</script>
