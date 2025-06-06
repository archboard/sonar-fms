<template>
  <ModalWrapper :show="show" @close="close">
    <DropIn @after-leave="$emit('close')">
      <div v-if="show" ref="modal" :class="modalSize" class="w-full inline-block align-middle bg-white dark:bg-gray-600 rounded-2xl text-left shadow-xl transform transition-all sm:my-8" role="dialog" aria-modal="true" aria-labelledby="modal-headline">
        <div class="hidden sm:block absolute top-0 right-0 pt-4 pr-3">
          <button @click.prevent="close" type="button" class="bg-white dark:bg-gray-600 rounded-full text-gray-400 hover:text-gray-500 focus:bg-gray-50 focus:outline-none focus:ring focus:ring-gray-300 dark:focus:ring-gray-500 transition ease-in-out">
            <span class="sr-only">Close</span>
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div class="sm:flex sm:items-start px-4 pt-5 pb-4 sm:p-6">
          <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
            <ModalHeadline v-if="headline">
              {{ headline }}
            </ModalHeadline>
            <div>
              <slot/>
            </div>
          </div>
        </div>
        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 flex flex-col space-y-2 sm:space-y-0 sm:flex-row-reverse rounded-b-2xl">
          <slot name="actions">
            <Button @click.prevent="performAction" type="button" :loading="actionLoading" :color="actionColor" class="sm:ml-2 text-sm" ref="action">
              {{ computedActionText }}
            </Button>
            <Button @click.prevent="close" type="button" color="white" class="text-sm">
              {{ __('Close') }}
            </Button>
          </slot>
        </div>
      </div>
    </DropIn>
  </ModalWrapper>
</template>

<script>
import { defineComponent } from 'vue'
import { disableBodyScroll, clearAllBodyScrollLocks } from 'body-scroll-lock'
import Button from '@/components/Button.vue'
import ModalHeadline from '@/components/modals/ModalHeadline.vue'
import ModalWrapper from '@/components/modals/ModalWrapper.vue'
import DropIn from '@/components/transitions/DropIn.vue'

export default defineComponent({
  components: {
    DropIn,
    ModalWrapper,
    ModalHeadline,
    Button
  },
  emits: ['close', 'action'],

  props: {
    headline: String,
    actionText: String,
    actionLoading: {
      type: Boolean,
      default: false
    },
    actionColor: {
      type: String,
      default: 'primary',
    },
    autoClose: {
      type: Boolean,
      default: true
    },
    size: {
      type: String,
      default: 'lg'
    },
    initialFocus: {
      type: Object,
    }
  },

  data () {
    return {
      show: false,
    }
  },

  computed: {
    computedActionText () {
      return this.actionText || this.__('Save')
    },

    modalSize () {
      const modalSizes = {
        xs: 'sm:max-w-xs',
        sm: 'sm:max-w-sm',
        md: 'sm:max-w-md',
        lg: 'sm:max-w-lg',
        xl: 'sm:max-w-xl',
        '2xl': 'sm:max-w-2xl',
        '3xl': 'sm:max-w-3xl',
        '4xl': 'sm:max-w-4xl',
      }

      return modalSizes[this.size]
    },

    localActionText () {
      return this.actionText || 'Confirm'
    }
  },

  mounted () {
    this.show = true

    this.attachListener()

    this.$nextTick(() => {
      disableBodyScroll(this.$refs.modal)

      if (this.initialFocus) {
        this.initialFocus?.$el.focus()
      } else if (this.$refs.action) {
        this.$refs.action.$el.focus()
      }
    })
  },

  unmounted () {
    clearAllBodyScrollLocks()
    this.detachListener()
  },

  methods: {
    performAction () {
      this.$emit('action', this.close)

      if (this.autoClose) {
        this.close()
      }
    },

    close () {
      this.show = false
    },

    isLevel (level) {
      return this.level === level
    },

    listener (e) {
      if (e.key === 'Escape') {
        e.stopPropagation()
        this.close()
      }
    },

    attachListener () {
      document.addEventListener('keydown', this.listener)
    },

    detachListener () {
      document.removeEventListener('keydown', this.listener)
    },
  }
})
</script>
