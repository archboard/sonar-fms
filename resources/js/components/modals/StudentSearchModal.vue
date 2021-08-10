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
            <div class="px-4 pt-5 pb-4 sm:p-6">
              <Label for="student-search-term">{{ __('Seach for student') }}</Label>
              <Input v-model="term" id="student-search-term" :placeholder="__('Search by name, email or student number')" type="search" autofocus />

              <Loader v-if="fetchingStudents" class="pb-0" />

              <FadeIn>
                <div v-if="students.length > 0" class="mt-4 w-full space-y-1">
                  <a
                    v-for="student in students"
                    :key="student.id"
                    @click.prevent="selected(student)"
                    href="#"
                    class="flex justify-between text-sm w-full rounded items-center py-2 px-4 hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                  >
                    <div>
                      {{ student.full_name }} <span class="text-gray-500 dark:text-gray-400">({{ student.student_number }})</span>
                    </div>
                    <div>
                      {{ student.grade_level_formatted }}
                    </div>
                  </a>
                </div>
              </FadeIn>
            </div>
          </div>
        </transition>
      </div>
    </div>
  </teleport>
</template>

<script>
import { defineComponent, nextTick, onMounted, onUnmounted, ref, watchEffect } from 'vue'
import { disableBodyScroll, clearAllBodyScrollLocks } from 'body-scroll-lock'
import clickaway from '@/directives/clickaway'
import fetchesStudents from '@/composition/fetchesStudents'
import Input from '@/components/forms/Input'
import Label from '@/components/forms/Label'
import FadeIn from '@/components/transitions/FadeIn'
import Loader from '@/components/Loader'
import debounce from 'lodash/debounce'

export default defineComponent({
  directives: {
    clickaway,
  },
  components: {
    Loader,
    FadeIn,
    Input,
    Label,
  },
  emits: ['close', 'selected'],

  setup (props, { emit }) {
    const show = ref(false)
    const term = ref('')
    const { students, search, fetchingStudents } = fetchesStudents()
    const modal = ref(null)
    const close = () => {
      show.value = false
    }
    const selected = student => {
      emit('selected', student)
      close()
    }
    const listener = (e) => {
      if (e.key === 'Escape') {
        e.stopPropagation()
        close()
      }
    }
    const runSearch = debounce(() => {
      search({ s: term.value })
    }, 500)

    watchEffect(() => {
      if (term.value) {
        runSearch()
      } else {
        students.value = []
      }
    })

    watchEffect(() => {
      if (show.value) {
        nextTick(() => {
          disableBodyScroll(modal.value)
        })
      } else {
        clearAllBodyScrollLocks()
      }
    })

    onMounted(() => {
      document.addEventListener('keydown', listener)
      show.value = true
    })

    onUnmounted(() => {
      document.removeEventListener('keydown', listener)
    })

    return {
      show,
      modal,
      close,
      students,
      term,
      selected,
      fetchingStudents,
    }
  },
})
</script>
