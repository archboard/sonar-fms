<template>
  <div class="h-screen flex overflow-hidden bg-gray-100 dark:bg-gray-900" data-cy="page">
    <!-- Off-canvas menu for mobile, show/hide based on off-canvas menu state. -->
    <div v-if="showMenuWrapper" class="md:hidden">
      <div class="fixed inset-0 flex z-40">
        <!-- Off-canvas menu overlay, show/hide based on off-canvas menu state. -->
        <transition
          enter-active-class="transition-opacity ease-linear duration-300"
          enter-from-class="opacity-0"
          enter-to-class="opacity-100"
          leave-active-class="transition-opacity ease-linear duration-300"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
          @after-leave="showMenuWrapper = false"
        >
          <div v-if="showMenu" class="fixed inset-0" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-600 opacity-75"></div>
          </div>
        </transition>
        <!-- Off-canvas menu, show/hide based on off-canvas menu state. -->
        <transition
          enter-active-class="transition ease-in-out duration-300 transform"
          enter-from-class="-translate-x-full"
          enter-to-class="translate-x-0"
          leave-active-class="transition ease-in-out duration-300 transform"
          leave-from-class="translate-x-0"
          leave-to-class="-translate-x-full"
        >
          <div v-if="showMenu" class="relative flex-1 flex flex-col max-w-xs w-full pt-5 pb-4 bg-gradient-to-t from-primary-500 to-primary-700 dark:from-primary-700 dark:to-primary-900">
            <div class="absolute top-0 right-0 -mr-12 pt-2">
              <button @click.prevent="showMenu = false" class="ml-1 flex items-center justify-center h-10 w-10 rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
                <span class="sr-only">Close sidebar</span>
                <!-- Heroicon name: outline/x -->
                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            <div class="flex-shrink-0 flex items-center px-4">
              <img class="h-8 w-auto" src="https://tailwindui.com/img/logos/workflow-logo-indigo-300-mark-white-text.svg" alt="Workflow">
            </div>
            <div class="mt-5 flex-1 h-0 overflow-y-auto">
              <nav class="px-2 space-y-1">
                <!-- Current: "bg-primary-900 dark:bg-primary-600 text-white", Default: "text-primary-100 hover:bg-primary-800 dark:hover:bg-primary-600" -->
                <a href="#" class="bg-primary-900 dark:bg-primary-600 text-white text-white group flex items-center px-2 py-2 text-base font-medium rounded-md transition">
                  <!-- Current: "text-primary-100", Default: "text-primary-300 group-hover:text-primary-200" -->
                  <svg class="text-primary-100 mr-4 h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                  </svg>
                  Dashboard
                </a>

                <a href="#" class="text-primary-100 hover:bg-primary-800 dark:hover:bg-primary-600 group flex items-center px-2 py-2 text-base font-medium rounded-md transition">
                  <!-- Heroicon name: outline/users -->
                  <svg class="text-primary-300 group-hover:text-primary-200 mr-4 h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                  </svg>
                  Team
                </a>

                <a href="#" class="text-primary-100 hover:bg-primary-600 group flex items-center px-2 py-2 text-base font-medium rounded-md">
                  <!-- Heroicon name: outline/folder -->
                  <svg class="mr-4 h-6 w-6 text-primary-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                  </svg>
                  Projects
                </a>

                <a href="#" class="text-primary-100 hover:bg-primary-600 group flex items-center px-2 py-2 text-base font-medium rounded-md">
                  <!-- Heroicon name: outline/calendar -->
                  <svg class="mr-4 h-6 w-6 text-primary-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  Calendar
                </a>

                <a href="#" class="text-primary-100 hover:bg-primary-600 group flex items-center px-2 py-2 text-base font-medium rounded-md">
                  <!-- Heroicon name: outline/inbox -->
                  <svg class="mr-4 h-6 w-6 text-primary-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                  </svg>
                  Documents
                </a>

                <a href="#" class="text-primary-100 hover:bg-primary-600 group flex items-center px-2 py-2 text-base font-medium rounded-md">
                  <!-- Heroicon name: outline/chart-bar -->
                  <svg class="mr-4 h-6 w-6 text-primary-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                  </svg>
                  Reports
                </a>
              </nav>
            </div>
          </div>
        </transition>

        <transition
          enter-active-class="transition duration-300"
          leave-active-class="transition duration-300"
        >
          <div v-if="showMenu" class="flex-shrink-0 w-14" aria-hidden="true">
            <!-- Dummy element to force sidebar to shrink to fit close icon -->
          </div>
        </transition>
      </div>
    </div>

    <!-- Static sidebar for desktop -->
    <div class="hidden bg-gradient-to-t from-primary-500 to-primary-700 dark:from-primary-700 dark:to-primary-900 md:flex md:flex-shrink-0">
      <div class="flex flex-col w-64">
        <!-- Sidebar component, swap this element with another sidebar if you like -->
        <div class="flex flex-col flex-grow pt-5 pb-4 overflow-y-auto">
          <div class="flex items-center flex-shrink-0 px-4">
            <img class="h-8 w-auto" src="https://tailwindui.com/img/logos/workflow-logo-indigo-300-mark-white-text.svg" alt="Workflow">
          </div>
          <div class="mt-5 flex-1 flex flex-col">
            <nav class="flex-1 px-2 space-y-8">
              <div class="space-y-1">
                <inertia-link
                  v-for="link in props.mainNav"
                  :href="link.route"
                  class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition"
                  :class="{
                    'bg-primary-900 dark:bg-primary-600 text-white': link.active,
                    'text-primary-100 hover:bg-primary-800 dark:hover:bg-primary-600': !link.active,
                  }"
                >
                  <svg
                    class="mr-3 h-6 w-6 transition"
                    :class="{
                      'text-primary-100': link.active,
                      'text-primary-300 group-hover:text-primary-200': !link.active,
                    }"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    aria-hidden="true"
                    v-html="link.icon"
                  />
                  {{ link.label }}
                </inertia-link>
              </div>

              <div class="space-y-1">
                <h3 class="px-3 text-xs font-semibold text-gray-200 uppercase tracking-wider" id="projects-headline">
                  {{ __('Settings') }}
                </h3>
                <div class="space-y-1" role="group" aria-labelledby="projects-headline">
                  <inertia-link
                    v-for="link in props.subNav"
                    :href="link.route"
                    class="group flex items-center px-3 py-2 text-sm font-medium rounded-md"
                    :class="{
                      'text-gray-50 bg-primary-600': link.active,
                      'text-gray-100 hover:text-gray-50 hover:bg-primary-600': !link.active,
                    }"
                  >
                    <span class="truncate">
                      {{ link.label }}
                    </span>
                  </inertia-link>
                </div>
              </div>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <div class="flex flex-col w-0 flex-1 overflow-hidden">
      <div class="relative z-10 flex-shrink-0 flex h-16 bg-white dark:bg-gray-900 shadow dark:shadow-none">
        <button @click.prevent="showMenuWrapper = true" class="px-4 border-r border-gray-200 dark:border-gray-600 text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500 md:hidden">
          <span class="sr-only">Open sidebar</span>
          <!-- Heroicon name: outline/menu-alt-2 -->
          <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
          </svg>
        </button>
        <div class="flex-1 px-4 flex justify-between">
          <div class="flex-1 flex">
            <form class="w-full flex md:ml-0" action="#" method="GET">
              <label for="search_field" class="sr-only">Search</label>
              <div class="relative w-full text-gray-400 focus-within:text-gray-600 dark:focus-within:text-gray-200">
                <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none">
                  <!-- Heroicon name: solid/search -->
                  <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                  </svg>
                </div>
                <input id="search_field" class="block w-full h-full pl-8 pr-3 py-2 border-transparent bg-transparent text-gray-900 dark:text-gray-100 placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-0 focus:border-transparent sm:text-sm" placeholder="Search" type="search" name="search">
              </div>
            </form>
          </div>
          <div class="ml-4 flex items-center md:ml-6 space-x-2">
            <button class="bg-white dark:bg-gray-900 p-1 rounded-full text-gray-400 hover:text-gray-200 dark:hover-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-900 focus:ring-primary-500 transition">
              <span class="sr-only">View notifications</span>
              <!-- Heroicon name: outline/bell -->
              <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
            </button>
            <button @click.prevent="isDark = !isDark" :title="__('Change theme color')" class="bg-white dark:bg-gray-900 p-1 rounded-full text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-900 focus:ring-primary-500 transition">
              <span class="sr-only">Change theme</span>
              <svg v-if="isDark" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
              <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </button>

            <!-- Profile dropdown -->
<!--            <div class="ml-3 relative">-->
<!--              <div>-->
<!--                <button type="button" class="max-w-xs bg-white flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500" id="user-menu" aria-expanded="false" aria-haspopup="true">-->
<!--                  <span class="sr-only">Open user menu</span>-->
<!--                  <img class="h-8 w-8 rounded-full" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixqx=407eZrYjvO&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="">-->
<!--                </button>-->
<!--              </div>-->

<!--              &lt;!&ndash;-->
<!--                Dropdown menu, show/hide based on menu state.-->

<!--                Entering: "transition ease-out duration-100"-->
<!--                  From: "transform opacity-0 scale-95"-->
<!--                  To: "transform opacity-100 scale-100"-->
<!--                Leaving: "transition ease-in duration-75"-->
<!--                  From: "transform opacity-100 scale-100"-->
<!--                  To: "transform opacity-0 scale-95"-->
<!--              &ndash;&gt;-->
<!--              <div class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="user-menu">-->
<!--                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Your Profile</a>-->

<!--                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Settings</a>-->

<!--                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Sign out</a>-->
<!--              </div>-->
<!--            </div>-->
          </div>
        </div>
      </div>

      <main class="flex-1 relative overflow-y-auto focus:outline-none" tabindex="0">
        <div class="py-6 space-y-6">
          <div v-if="props.title" class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            <div class="md:flex md:items-center md:justify-between">
              <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 sm:text-3xl sm:truncate">
                  {{ props.title }}
                </h2>
              </div>
              <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
                <slot name="actions" />
              </div>
            </div>
          </div>
          <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            <slot/>
          </div>
        </div>
      </main>
    </div>

    <Notifications />
  </div>
</template>

<script>
import { defineComponent, ref, watch, nextTick, } from 'vue'
import Notifications from '../components/Notifications'
import { usePage } from '@inertiajs/inertia-vue3'

export default defineComponent({
  components: {
    Notifications
  },

  setup () {
    const isDark = ref(localStorage.theme === 'dark')
    const showMenu = ref(false)
    const showMenuWrapper = ref(false)
    const page = usePage()
    watch(isDark, window.changeTheme)
    watch(showMenuWrapper, (newVal) => {
      if (newVal) {
        nextTick(() => {
          showMenu.value = true
        })
      }
    })

    return {
      isDark,
      showMenu,
      showMenuWrapper,
      props: page.props,
    }
  }
})
</script>
