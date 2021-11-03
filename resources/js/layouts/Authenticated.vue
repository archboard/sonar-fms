<template>
  <div class="min-h-screen flex bg-gray-100 dark:bg-gray-900" data-cy="page">
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
                <XIcon class="h-6 w-6 text-white" />
              </button>
            </div>
            <div class="flex-shrink-0 flex items-center px-4">
              <img class="h-8 w-auto" src="/images/sonar-fms-light.svg" alt="Sonar FMS">
            </div>
            <div class="mt-5 flex-1 h-0 overflow-y-auto">
              <nav class="px-2 space-y-6">
                <div class="space-y-1">
                  <inertia-link
                    v-for="link in props.mainNav"
                    :href="link.route"
                    class="group flex items-center px-2 py-2 text-base font-medium rounded-md transition"
                    :class="{
                      'bg-primary-900 dark:bg-primary-600 text-white': link.active,
                      'text-gray-200 hover:text-gray-100 hover:bg-primary-800 dark:hover:bg-primary-600': !link.active,
                    }"
                  >
                    <svg
                      class="mr-3 h-6 w-6 transition"
                      :class="{
                        'text-primary-100': link.active,
                        'text-primary-200 group-hover:text-primary-100': !link.active,
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
                  <h3 class="px-3 text-xs font-semibold text-gray-200 uppercase tracking-wider">
                    {{ __('Settings') }}
                  </h3>
                  <div class="space-y-1" role="group">
                    <inertia-link
                      v-for="link in props.subNav"
                      :href="link.route"
                      class="group flex items-center px-3 py-2 text-base font-medium rounded-md"
                      :class="{
                        'text-gray-50 bg-primary-900 dark:bg-primary-600': link.active,
                        'text-gray-100 hover:text-gray-50 hover:bg-primary-700 dark:hover:bg-primary-600': !link.active,
                      }"
                    >
                      <span class="truncate">
                        {{ link.label }}
                      </span>
                    </inertia-link>
                  </div>
                </div>

                <LocaleSelector class="px-2 mt-4" />
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
    <div class="hidden md:flex md:flex-shrink-0 w-64">
      <div class="flex flex-col bg-gradient-to-t from-primary-500 to-primary-700 dark:from-primary-700 dark:to-primary-900 w-64 h-screen fixed z-10 top-0 bottom-0">
        <!-- Sidebar component, swap this element with another sidebar if you like -->
        <div class="flex flex-col flex-grow pt-5 pb-4 overflow-y-auto">
          <div class="flex items-center flex-shrink-0 px-4">
            <img class="h-8 w-auto" src="/images/sonar-fms-light.svg" alt="Sonar FMS">
          </div>

          <SchoolSwitcher v-if="props.user.schools.length > 1" class="px-2 mt-6" />

          <div class="mt-5 flex-1 flex flex-col">
            <nav class="flex-1 px-2 space-y-8">
              <div class="space-y-1">
                <inertia-link
                  v-for="link in props.mainNav"
                  :href="link.route"
                  class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition"
                  :class="{
                    'bg-primary-900 dark:bg-primary-600 text-white': link.active,
                    'text-gray-200 hover:text-gray-100 hover:bg-primary-800 dark:hover:bg-primary-600': !link.active,
                  }"
                >
                  <svg
                    class="mr-3 h-6 w-6 transition"
                    :class="{
                      'text-primary-100': link.active,
                      'text-primary-200 group-hover:text-primary-100': !link.active,
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
                <h3 class="px-3 text-xs font-semibold text-gray-200 uppercase tracking-wider">
                  {{ __('Settings') }}
                </h3>
                <div class="space-y-1" role="group">
                  <inertia-link
                    v-for="link in props.subNav"
                    :href="link.route"
                    class="group flex items-center px-3 py-2 text-sm font-medium rounded-md"
                    :class="{
                      'text-gray-50 bg-primary-900 dark:bg-primary-600': link.active,
                      'text-gray-100 hover:text-gray-50 hover:bg-primary-700 dark:hover:bg-primary-600': !link.active,
                    }"
                  >
                    <span class="truncate">
                      {{ link.label }}
                    </span>
                  </inertia-link>
                </div>
              </div>

              <LocaleSelector class="px-2 mt-4" />
            </nav>
          </div>
        </div>
      </div>
    </div>

    <div class="flex flex-col justify-between w-0 flex-1 min-h-screen">
      <div
        class="relative z-30 flex-shrink-0 flex h-16 bg-white dark:bg-gray-800"
        :class="{
          'shadow': props.breadcrumbs.length === 0
        }"
      >
        <button @click.prevent="showMenuWrapper = true" class="px-4 text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500 md:hidden">
          <span class="sr-only">Open sidebar</span>
          <!-- Heroicon name: outline/menu-alt-2 -->
          <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
          </svg>
        </button>
        <div class="flex-1 px-4 flex justify-between">
          <div class="flex-1 flex">
            <TopSearch />
          </div>
          <div class="ml-4 flex items-center md:ml-6 space-x-2">
            <button class="bg-transparent p-1 rounded-full text-gray-400 hover:text-gray-200 dark:hover-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-900 focus:ring-primary-500 transition">
              <span class="sr-only">View notifications</span>
              <!-- Heroicon name: outline/bell -->
              <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
            </button>
            <button @click.prevent="darkStore.toggle()" :title="__('Change theme color')" class="bg-transparent p-1 rounded-full text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-900 focus:ring-primary-500 transition">
              <span class="sr-only">Change theme</span>
              <svg v-if="darkStore.state.isDark" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
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

      <nav v-if="props.breadcrumbs.length > 0" class="flex bg-white dark:bg-gray-800 py-3 px-4 shadow" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-4">
          <li>
            <div>
              <inertia-link :href="$route('home')" class="text-gray-400 hover:text-gray-500 dark:text-gray-300 dark:hover:text-gray-200 transition">
                <HomeIcon class="flex-shrink-0 h-5 w-5" />
                <span class="sr-only">Home</span>
              </inertia-link>
            </div>
          </li>

          <li
            v-for="crumb in props.breadcrumbs"
            :key="crumb.route"
          >
            <div class="flex items-center">
              <svg class="flex-shrink-0 h-5 w-5 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
              </svg>
              <inertia-link :href="crumb.route" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 transition">
                {{ crumb.label }}
              </inertia-link>
            </div>
          </li>
        </ol>
      </nav>

      <main class="relative flex-1 focus:outline-none" tabindex="0">
        <slot name="content">
          <div class="py-6 space-y-6">
            <div v-if="props.title" class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
              <div class="md:flex md:items-start md:justify-between">
                <div class="flex-1 min-w-0">
                  <h2 class="flex items-center text-2xl font-bold sm:text-3xl sm:truncate leading-10 sm:leading-10" data-cy="page-title">
                    {{ props.title }}
                    <slot name="inTitle" />
                  </h2>
                  <slot name="afterTitle" />
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
        </slot>
      </main>

      <footer>
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 lg:max-w-7xl mt-8">
          <div class="border-t border-gray-200 dark:border-gray-500 py-8 text-sm text-gray-500 text-center sm:text-left">
            <span class="block sm:inline">&copy; {{ (new Date).getFullYear() }} <a href="https://archboard.io" target="_blank" class="hover:underline">Archboard, LLC</a>.</span>
            <span class="block sm:inline"> All rights reserved.</span>
          </div>
        </div>
      </footer>

    </div>

    <Notifications />
  </div>
</template>

<script>
import { defineComponent, ref, watch, nextTick } from 'vue'
import Notifications from '@/components/Notifications'
import { usePage } from '@inertiajs/inertia-vue3'
import TopSearch from '@/components/TopSearch'
import SchoolSwitcher from '@/components/SchoolSwitcher'
import LocaleSelector from '@/components/LocaleSelector'
import darkStore from '@/stores/theme'
import { HomeIcon } from '@heroicons/vue/solid'
import { XIcon } from '@heroicons/vue/outline'
import setsTitle from '@/composition/setsTitle'

export default defineComponent({
  components: {
    SchoolSwitcher,
    LocaleSelector,
    TopSearch,
    Notifications,
    HomeIcon,
    XIcon,
  },

  setup () {
    setsTitle()
    const showMenu = ref(false)
    const showMenuWrapper = ref(false)
    const page = usePage()

    watch(showMenuWrapper, (newVal) => {
      if (newVal) {
        nextTick(() => {
          showMenu.value = true
        })
      }
    })

    return {
      darkStore,
      showMenu,
      showMenuWrapper,
      props: page.props,
    }
  }
})
</script>
