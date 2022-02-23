<template>
  <Modal
    @close="$emit('close')"
    ref="modal"
    size="sm"
  >
    <div>
      <slot name="icon">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100">
          <ExclamationIcon class="w-6 h-6 text-yellow-600" />
        </div>
      </slot>

      <div class="mt-3 text-center sm:mt-5 w-full">
        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-headline">
          <slot name="headline">
            {{ __('Are you sure?') }}
          </slot>
        </h3>
        <div class="mt-2">
          <p class="text-sm text-gray-500 dark:text-gray-300">
            <slot name="content">
              {{ __('This action cannot be undone.') }}
            </slot>
          </p>
        </div>
      </div>
    </div>

    <template v-slot:actions>
      <div class="space-y-2 sm:space-y-0 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense w-full">
        <Button type="button" @click.prevent="confirmed" class="w-full" color="yellow" ref="action">
          <slot name="actionText">
            {{ __('Do it!') }}
          </slot>
        </Button>
        <Button type="button" color="white" @click.prevent="modal.close" class="w-full">
          {{ __('Never mind') }}
        </Button>
      </div>
    </template>
  </Modal>
</template>

<script>
import { defineComponent, nextTick, onMounted, ref } from 'vue'
import Modal from '@/components/Modal'
import Button from '@/components/Button'
import { ExclamationIcon } from '@heroicons/vue/outline'

export default defineComponent({
  components: {
    Button,
    Modal,
    ExclamationIcon,
  },
  emits: ['close', 'confirmed'],

  setup (props, { emit }) {
    const action = ref(null)
    const modal = ref(null)
    const confirmed = () => {
      emit('confirmed')
      modal.value.close()
    }

    onMounted(() => {
      nextTick(() => {
        action.value?.$el?.focus()
      })
    })

    return {
      modal,
      action,
      confirmed,
    }
  }
})
</script>
