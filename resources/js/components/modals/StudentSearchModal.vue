<template>
  <teleport to="body">
    <div class="fixed z-10 inset-0 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
        <transition
          enter-active-class="duration-300 ease-out"
          enter-from-class="opacity-0"
          enter-to-class="opacity-100"
          leave-active-class="duration-200 ease-in"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
        >
          <div v-if="show" class="fixed inset-0 transition-opacity" style="backdrop-filter: blur(5px);" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-800 opacity-75"></div>
          </div>
        </transition>

        <transition
          enter-active-class="ease-out duration-300"
          enter-from-class="opacity-0 -translate-y-5"
          enter-to-class="opacity-100 translate-y-0"
          leave-active-class="ease-in duration-200"
          leave-from-class="opacity-100 translate-y-0"
          leave-to-class="opacity-0 -translate-y-5"
          @after-leave="$emit('close')"
        >
          <div v-if="show" v-clickaway="close" ref="modal" class="inline-block max-w-md align-middle bg-white dark:bg-gray-600 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:w-full" role="dialog" aria-modal="true">
            <div class="sm:flex sm:items-start px-4 pt-5 pb-4 sm:p-6">
              <Typeahead />
            </div>
          </div>
        </transition>
      </div>
    </div>
  </teleport>
</template>

<script>
import { defineComponent, nextTick, onMounted, onUnmounted, ref } from 'vue'
import { disableBodyScroll, clearAllBodyScrollLocks } from 'body-scroll-lock'
import clickaway from '@/directives/clickaway'
import Typeahead from '@/components/forms/Typeahead'

export default defineComponent({
  directives: {
    clickaway,
  },
  components: {
    Typeahead,
  },
  emits: ['close', 'selected'],

  props: {
  },

  setup (props, { emit }) {
    const show = ref(true)
    const modal = ref(null)
    const close = () => {
      show.value = false
    }
    const listener = (e) => {
      if (e.key === 'Escape') {
        e.stopPropagation()
        this.close()
      }
    }

    onMounted(() => {
      show.value = true
      document.addEventListener('keydown', listener)

      nextTick(() => {
        disableBodyScroll(modal.value)
      })
    })

    onUnmounted(() => {
      clearAllBodyScrollLocks()
      document.removeEventListener('keydown', listener)
    })

    return {
      show,
      modal,
      close,
    }
  },
})
</script>
