<template>
  <div>
    <h3 v-if="title" class="text-lg leading-6 font-medium">
      {{ title }}
    </h3>
    <dl
      class="grid grid-cols-1 gap-5"
      :class="[
        columnSize,
        title ? 'mt-5' : '',
      ]"
    >
      <div
        v-for="stat in stats"
        :key="stat.label"
        class="px-4 py-5 bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden sm:p-6"
        :class="stat.classes || ''"
      >
        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
          {{ stat.label }}
        </dt>
        <dd class="mt-1 text-3xl font-semibold">
          {{ stat.value }}
        </dd>
      </div>
    </dl>
  </div>

</template>

<script>
import { defineComponent } from 'vue'

export default defineComponent({
  components: {
  },
  props: {
    title: String,
    stats: {
      type: Array,
      default: () => ([]),
    },
  },

  setup (props) {
    const columns = {
      1: 'sm:grid-cols-1',
      2: 'sm:grid-cols-2',
      3: 'sm:grid-cols-3',
    }

    return {
      columnSize: columns[props.stats.length || 1],
    }
  }
})
</script>
