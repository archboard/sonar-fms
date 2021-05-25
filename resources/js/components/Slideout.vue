<template>
  <teleport to="body">
    <div class="fixed z-10 inset-0 slideout">
      <transition
        enter-active-class="duration-300 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div v-if="show" class="fixed inset-0 transition-opacity" style="backdrop-filter: blur(5px);" aria-hidden="true">
          <div class="absolute inset-0 bg-gray-800 opacity-75" @click="close"></div>
        </div>
      </transition>

      <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex sm:pl-16">
        <transition
          enter-active-class="transform transition ease-in-out duration-500 sm:duration-700"
          enter-from-class="translate-x-full"
          enter-to-class="translate-x-0"
          leave-active-class="transform transition ease-in-out duration-500 sm:duration-700"
          leave-from-class="translate-x-0"
          leave-to-class="translate-x-full"
          @after-leave="$emit('close')"
        >
          <div v-if="show" ref="slideout" class="w-screen max-w-3xl relative">
            <div class="flex flex-col h-full bg-white dark:bg-gray-600 shadow-xl">
              <!-- Header -->
              <div class="px-4 py-6 bg-gray-50 dark:bg-gray-700 sm:px-6">
                <div class="flex items-start justify-between space-x-3">
                  <div class="space-y-1">
                    <slot name="header"></slot>
                  </div>
                  <div class="h-7 flex items-center">
                    <button type="button" class="rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500" @click="close">
                      <span class="sr-only">Close panel</span>
                      <XIcon class="h-6 w-6" aria-hidden="true" />
                    </button>
                  </div>
                </div>
              </div>

              <!-- Body -->
              <div class="flex-1 py-6 relative flex-1 px-4 sm:px-6 h-full overflow-y-scroll">
                <slot />
              </div>

              <!-- Action buttons -->
              <div class="flex-shrink-0 px-4 py-5 sm:px-6 bg-gray-50 dark:bg-gray-700">
                <div class="space-x-3 flex justify-end">
                  <slot name="actions" :close="close" :action="performAction">
                    <Button type="button" color="white" @click="close">
                      {{ __('Cancel') }}
                    </Button>
                    <Button type="button" @click.prevent="performAction" :loading="processing">
                      {{ __('Save') }}
                    </Button>
                  </slot>
                </div>
              </div>
            </div>
          </div>
        </transition>
      </div>
    </div>
  </teleport>
</template>

<script>
import { defineComponent, nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
import { XIcon } from '@heroicons/vue/outline'
import { disableBodyScroll, clearAllBodyScrollLocks } from 'body-scroll-lock'
import Button from './Button'

export default defineComponent({
  components: {
    Button,
    XIcon,
  },
  emits: ['close', 'action'],
  props: {
    autoClose: {
      type: Boolean,
      default: false,
    },
    processing: {
      type: Boolean,
      default: false
    }
  },

  setup (props, { emit }) {
    const show = ref(false)
    const slideout = ref(null)

    const close = () => {
      show.value = false
    }
    const performAction = () => {
      emit('action', close)

      if (props.autoClose) {
        close()
      }
    }
    const listener = e => {
      if (e.key === 'Escape') {
        e.stopPropagation()
        close()
      }
    }

    onMounted(() => {
      show.value = true
      document.addEventListener('keydown', listener)

      nextTick(() => {
        disableBodyScroll(slideout.value)
      })
    })
    onBeforeUnmount(() => {
      clearAllBodyScrollLocks()
      document.removeEventListener('keydown', listener)
    })

    return {
      show,
      slideout,
      performAction,
      close,
    }
  }
})
</script>
