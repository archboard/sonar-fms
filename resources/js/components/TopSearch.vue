<template>
  <form
    class="w-full flex md:ml-0 relative"
    @submit="doSearch"
    @mouseover="hoveringSearch = true"
    @mouseleave="hoveringSearch = false"
  >
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
        @focus="focusingInput = true"
        @blur="focusingInput = false"
        @keydown.esc="searchResults = {}"
        @keypress.enter.prevent="goToPage"
        @keyup.down.prevent="goToResult(1)"
        @keyup.up.prevent="goToResult(-1)"
      >
    </div>

    <DropIn>
      <div
        v-if="showResults"
        class="absolute z-20 w-full top-full"
      >
        <div class="mt-2 bg-gray-100 dark:bg-gray-700 space-y-4 rounded-lg shadow-lg p-3 overflow-hidden">
          <div v-for="(results, type) in searchResults" :key="type">
            <div class="px-3 py-1 text-black dark:text-white text-sm font-bold text-xs uppercase tracking-widest">{{ type }}</div>
            <ul class="space-y-1">
              <li
                v-for="result in results"
                :key="result.url"
                @mouseover="currentItem = result.url"
              >
                <SearchResult :result="result" :active="currentItem === result.url" />
              </li>
            </ul>
          </div>
        </div>
      </div>
    </DropIn>
  </form>
</template>

<script>
import { defineComponent, ref, onMounted, onBeforeUnmount, watch, inject, computed } from 'vue'
import debounce from 'lodash/debounce'
import { SearchIcon } from '@heroicons/vue/outline'
import DropIn from '@/components/transitions/DropIn.vue'
import { Inertia } from '@inertiajs/inertia'
import SearchResult from '@/components/SearchResult.vue'

export default defineComponent({
  components: {
    SearchResult,
    DropIn,
    SearchIcon,
  },

  setup () {
    let body
    let main
    const focusingInput = ref(false)
    const hoveringSearch = ref(false)
    const search = ref(null)
    const searchTerm = ref('')
    const $http = inject('$http')
    const searchResults = ref({})
    const currentItem = ref('')
    const itemsList = ref([])
    const showResults = computed(() =>
      Object.keys(searchResults.value).length > 0 &&
      (focusingInput.value || hoveringSearch.value)
    )

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
      currentItem.value = ''
      itemsList.value = []

      if (!s) {
        searchResults.value = {}
        return
      }

      const { data } = await $http.get('/search', {
        params: { s }
      })

      for (const key in data) {
        for (const item of data[key]) {
          if (!currentItem.value) {
            currentItem.value = item.url
          }

          itemsList.value.push(item.url)
        }
      }

      searchResults.value = data
    }

    const currentIndex = computed(
      () => itemsList.value.indexOf(currentItem.value)
    )
    const goToPage = () => {
      if (currentItem.value) {
        Inertia.visit(currentItem.value)
      }
    }
    const goToResult = (diff) => {
      if (currentIndex.value === -1) {
        return
      }
      let nextIndex = currentIndex.value + diff

      if (nextIndex === itemsList.value.length) {
        nextIndex = 0
      } else if (nextIndex === -1) {
        nextIndex = itemsList.value.length - 1
      }

      currentItem.value = itemsList.value[nextIndex]
    }

    watch(searchTerm, debounce(doSearch, 500))

    return {
      search,
      searchTerm,
      doSearch,
      searchResults,
      currentItem,
      itemsList,
      goToPage,
      goToResult,
      showResults,
      hoveringSearch,
      focusingInput,
    }
  }
})
</script>
