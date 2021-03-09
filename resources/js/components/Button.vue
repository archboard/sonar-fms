<template>
  <component
    :is="component"
    class="border border-transparent font-medium rounded-md shadow hover:shadow-none text-white focus:outline-none transition ease-in-out duration-150 items-center justify-center"
    :class="[
      buttonSize,
      buttonColor,
      {
        'flex w-full': isBlock,
        'inline-flex items-center': !isBlock,
      }
    ]"
    :disabled="loading"
  >
    <svg v-if="loading" class="animate-spin -ms-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    <slot />
  </component>
</template>

<script>
  export default {
    props: {
      component: {
        type: String,
        default: 'button',
      },
      size: {
        type: String,
        default: 'base'
      },
      color: {
        type: String,
        default: 'primary'
      },
      loading: {
        type: Boolean,
        default: false
      },
      isBlock: {
        type: Boolean,
        default: false
      }
    },

    data () {
      return {
        sizes: {
          xs: `px-2.5 py-1.5 text-xs leading-4`,
          sm: `px-3 py-2 text-sm leading-4`,
          base: `px-4 py-2 text-sm leading-5`,
          lg: `px-4 py-2 text-base leading-6`,
          xl: `px-6 py-3 text-base leading-6`,
        },
        colors: {
          primary: 'bg-indigo-600 hover:bg-indigo-500 focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700',
          red: 'bg-red-600 hover:bg-red-500 focus:border-red-700 focus:shadow-outline-red active:bg-red-700',
          yellow: 'bg-yellow-600 hover:bg-yellow-500 focus:border-yellow-700 focus:shadow-outline-yellow active:bg-yellow-700',
          green: 'bg-green-600 hover:bg-green-500 focus:border-green-700 focus:shadow-outline-green active:bg-green-700',
          gray: 'bg-gray-600 hover:bg-gray-500 focus:border-gray-700 focus:shadow-outline-gray active:bg-gray-700',
          white: 'text-gray-700 bg-white hover:bg-gray-50 border-gray-300 focus:shadow-outline-gray active:bg-gray-100',
        }
      }
    },

    computed: {
      buttonSize () {
        return this.sizes[this.size] || this.sizes.base
      },

      buttonColor () {
        return this.colors[this.color] || this.colors.white
      }
    }
  }
</script>
