<template>
  <form class="w-full flex md:ml-0 relative" @submit="doSearch" @blur="searchResults = {}">
    <label for="search_field" class="sr-only">Search</label>
    <div class="relative w-full text-gray-400 focus-within:text-gray-600 dark:focus-within:text-gray-200">
      <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none">
        <SearchIcon class="h-5 w-5" />
      </div>
      <input
        v-model="searchTerm"
        ref="search"
        id="search_field"
        class="block w-full h-full pl-8 pr-3 py-2 border-transparent bg-transparent text-gray-900 dark:text-gray-100 placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-0 focus:border-transparent sm:text-sm"
        :placeholder="__('Type / to search')"
        type="search"
      >
    </div>

    <DropIn>
      <div
        v-if="Object.keys(searchResults).length > 0"
        class="absolute z-20 w-full top-full"
      >
        <div class="mt-2 bg-white dark:bg-gray-700 space-y-4 rounded-lg shadow-lg p-3 overflow-hidden">
          <div v-for="(results, type) in searchResults" :key="type">
            <div class="px-3 py-1 dark:text-gray-300 text-sm font-bold text-xs uppercase tracking-wider">{{ type }}</div>
            <ul class="space-y-1">
              <li
                v-for="result in results"
                :key="result.url"
                class="block rounded w-full px-3 py-1.5 hover:bg-gray-200 dark:hover:bg-gray-800 transition"
              >
                <InertiaLink :href="result.url" class="block w-full">{{ result.title }}</InertiaLink>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </DropIn>
  </form>
</template>

<script>
import { defineComponent, ref, onMounted, onBeforeUnmount, watch, inject } from 'vue'
import debounce from 'lodash/debounce'
import { SearchIcon } from '@heroicons/vue/outline'
import DropIn from '@/components/transitions/DropIn'

export default defineComponent({
  components: {
    DropIn,
    SearchIcon
  },

  setup () {
    let body
    let main
    const search = ref(null)
    const searchTerm = ref('')
    const $http = inject('$http')
    const searchResults = ref({})

    const focus = e => {
      if (
        e.key === '/' &&
        (
          e.target === body ||
          e.target === main
        )
      ) {
        search.value.focus()
      } else if (e.key === 'Escape' && e.target === search.value) {
        search.value.blur()
      }
    }

    document.addEventListener('keyup', focus)
    onMounted(() => {
      body = document.body
      main = document.querySelector('main')
    })
    onBeforeUnmount(() => {
      document.removeEventListener('keyup', focus)
    })

    const doSearch = async () => {
      const s = searchTerm.value

      if (!s) {
        searchResults.value = {}
        return
      }

      const { data } = await $http.get('/search', {
        params: { s }
      })

      searchResults.value = data
    }

    watch(searchTerm, debounce(doSearch, 500))

    return {
      search,
      searchTerm,
      doSearch,
      searchResults,
    }
  }
})
</script>
