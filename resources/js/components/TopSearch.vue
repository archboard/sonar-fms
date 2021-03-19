<template>
  <form class="w-full flex md:ml-0" action="#" method="GET">
    <label for="search_field" class="sr-only">Search</label>
    <div class="relative w-full text-gray-400 focus-within:text-gray-600 dark:focus-within:text-gray-200">
      <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none">
        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
          <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
        </svg>
      </div>
      <input v-model="searchTerm" ref="search" id="search_field" class="block w-full h-full pl-8 pr-3 py-2 border-transparent bg-transparent text-gray-900 dark:text-gray-100 placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-0 focus:border-transparent sm:text-sm" placeholder="Search" type="search" name="search">
    </div>
  </form>
</template>

<script>
import { defineComponent, ref, onMounted, onBeforeUnmount, watch } from 'vue'
import debounce from 'lodash/debounce'

export default defineComponent({
  setup () {
    let body
    let main
    const search = ref(null)
    const searchTerm = ref('')

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

    watch(searchTerm, debounce((newVal) => {
      console.log(newVal)
    }, 500))

    return {
      search,
      searchTerm,
    }
  }
})
</script>
