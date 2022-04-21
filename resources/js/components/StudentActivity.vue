<template>
  <section aria-labelledby="comments-title" class="mt-8 xl:mt-10">
    <div>
      <div class="divide-y divide-gray-300 dark:divide-gray-600">
        <div class="pb-4">
          <h2 id="comments-title" class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Comments') }}</h2>
        </div>
        <Loader v-if="fetching" />
        <div v-else class="pt-6">
          <div class="flow-root">
            <ul class="-mb-8">
              <li
                v-for="comment in comments"
                :key="comment.id"
                class="group"
              >
                <div class="relative pb-8">
                  <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-300" aria-hidden="true"></span>
                  <div class="relative flex items-start space-x-3">
                    <div class="relative px-1">
                      <div class="h-8 w-8 bg-gray-300 dark:bg-gray-800 rounded-full ring-8 ring-gray-100 dark:ring-gray-900 flex items-center justify-center">
                        <ChatAltIcon class="h-5 w-5 text-gray-500 dark:text-gray-400" />
                      </div>
                    </div>
                    <div class="min-w-0 flex-1 relative">
                      <div>
                        <div class="text-sm">
                          <span class="font-medium text-gray-900 dark:text-gray-100">{{ comment.user?.full_name }}</span>
                        </div>
                        <p class="mt-0.5 text-sm text-gray-500">
                          {{ displayDate(comment.created_at, 'full') }} ({{ comment.diff }})
                        </p>
                      </div>
                      <div class="mt-2 text-sm text-gray-700 dark:text-gray-300 comment-content" v-html="comment.markdown"></div>
                      <div v-if="comment.user?.uuid === user.uuid" class="hidden group-hover:flex items-start space-x-2 w-full absolute -bottom-6 inset-x-0 text-xs">
                        <Link is="button" @click.prevent="editComment(comment)">{{ __('Edit') }}</Link>
                        <Link is="button" @click.prevent="promptDelete(comment)">{{ __('Delete') }}</Link>
                      </div>
                    </div>
                  </div>
                </div>

              </li>

<!--              <li>-->
<!--                <div class="relative pb-8">-->
<!--                  <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-300" aria-hidden="true"></span>-->
<!--                  <div class="relative flex items-start space-x-3">-->
<!--                    <div>-->
<!--                      <div class="relative px-1">-->
<!--                        <div class="h-8 w-8 bg-gray-100 rounded-full ring-8 ring-gray-100 dark:ring-gray-900 flex items-center justify-center">-->
<!--                          &lt;!&ndash; Heroicon name: solid/user-circle &ndash;&gt;-->
<!--                          <svg class="h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">-->
<!--                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd" />-->
<!--                          </svg>-->
<!--                        </div>-->
<!--                      </div>-->
<!--                    </div>-->
<!--                    <div class="min-w-0 flex-1 py-1.5">-->
<!--                      <div class="text-sm text-gray-500">-->
<!--                        <a href="#" class="font-medium text-gray-900 dark:text-gray-100">Hilary Mahy</a>-->
<!--                        assigned-->
<!--                        <a href="#" class="font-medium text-gray-900 dark:text-gray-100">Kristin Watson</a> <span class="whitespace-nowrap">2d ago</span>-->
<!--                      </div>-->
<!--                    </div>-->
<!--                  </div>-->
<!--                </div>-->
<!--              </li>-->

<!--              <li>-->
<!--                <div class="relative pb-8">-->
<!--                  <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-300" aria-hidden="true"></span>-->
<!--                  <div class="relative flex items-start space-x-3">-->
<!--                    <div>-->
<!--                      <div class="relative px-1">-->
<!--                        <div class="h-8 w-8 bg-gray-100 rounded-full ring-8 ring-gray-100 dark:ring-gray-900 flex items-center justify-center">-->
<!--                          &lt;!&ndash; Heroicon name: solid/tag &ndash;&gt;-->
<!--                          <svg class="h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">-->
<!--                            <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />-->
<!--                          </svg>-->
<!--                        </div>-->
<!--                      </div>-->
<!--                    </div>-->
<!--                    <div class="min-w-0 flex-1 py-0">-->
<!--                      <div class="text-sm leading-8 text-gray-500">-->
<!--                        <span class="mr-0.5">-->
<!--                          <a href="#" class="font-medium text-gray-900 dark:text-gray-100">Hilary Mahy</a>-->
<!--                          added tags-->
<!--                        </span>-->
<!--                        <span class="mr-0.5 space-x-2">-->
<!--                          <OutlineBadge is="a" href="#" color="bg-rose-500">-->
<!--                            Bug-->
<!--                          </OutlineBadge>-->
<!--                          <OutlineBadge is="a" href="#" color="bg-indigo-500">-->
<!--                            Accessibility-->
<!--                          </OutlineBadge>-->
<!--                        </span> <span class="whitespace-nowrap">6h ago</span>-->
<!--                      </div>-->
<!--                    </div>-->
<!--                  </div>-->
<!--                </div>-->
<!--              </li>-->
            </ul>
          </div>
          <div class="mt-8">
            <div class="flex space-x-3">
              <div class="flex-shrink-0">
                <div class="relative px-1">
                  <div class="h-8 w-8 bg-gray-300 dark:bg-gray-800 rounded-full ring-8 ring-gray-100 dark:ring-gray-900 flex items-center justify-center">
                    <ChatAltIcon class="h-5 w-5 text-gray-500 dark:text-gray-400" />
                  </div>
                </div>
              </div>
              <div class="min-w-0 flex-1">
                <form @submit.prevent="saveComment">
                  <div>
                    <label for="comment" class="sr-only">Comment</label>
                    <Textarea v-model="form.comment" id="comment" rows="3" :placeholder="__('Leave a comment')"></Textarea>
                    <Error v-if="form.errors.comment">{{ form.errors.comment }}</Error>
                    <HelpText>{{ __("You can use Markdown to format your comments.") }}</HelpText>
                  </div>
                  <div class="mt-6 flex items-center justify-end space-x-4">
                    <Button :loading="form.processing" type="submit">
                      {{ __('Comment') }}
                    </Button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <CommentModal
      v-if="editing"
      @close="modalClosed"
      :comment="currentComment"
      :endpoint="`/students/${student.uuid}/comments/${currentComment.id}`"
      method="put"
    />
    <ConfirmationModal
      v-if="deleting"
      @close="modalClosed"
      @confirmed="deleteComment"
    />
  </section>
</template>

<script>
import { defineComponent, inject, ref } from 'vue'
import OutlineBadge from '@/components/OutlineBadge'
import Textarea from '@/components/forms/Textarea'
import Button from '@/components/Button'
import { useForm } from '@inertiajs/inertia-vue3'
import { ChatAltIcon } from '@heroicons/vue/solid'
import Error from '@/components/forms/Error'
import Loader from '@/components/Loader'
import HelpText from '@/components/HelpText'
import displaysDate from '@/composition/displaysDate'
import usesUser from '@/composition/usesUser'
import Link from '@/components/Link'
import CommentModal from '@/components/modals/CommentModal'
import ConfirmationModal from '@/components/modals/ConfirmationModal'
import { Inertia } from '@inertiajs/inertia'

export default defineComponent({
  components: {
    ConfirmationModal,
    CommentModal,
    Link,
    HelpText,
    Loader,
    Error,
    Button,
    OutlineBadge,
    Textarea,
    ChatAltIcon,
  },
  props: {
    student: Object,
  },

  setup ({ student }) {
    const { user } = usesUser()
    const fetching = ref(false)
    const editing = ref(false)
    const deleting = ref(false)
    const currentComment = ref({})
    const comments = ref([])
    const $http = inject('$http')
    const form = useForm({
      comment: ''
    })
    const { displayDate } = displaysDate()
    const saveComment = () => {
      form.post(`/students/${student.uuid}/comments`, {
        preserveScroll: true,
        onSuccess () {
          fetchComments()
          form.reset('comment')
        }
      })
    }
    const fetchComments = async () => {
      fetching.value = true
      const { data } = await $http.get(`/students/${student.uuid}/comments`)
      comments.value = data
      fetching.value = false
    }
    const editComment = comment => {
      currentComment.value = comment
      editing.value = true
    }
    const promptDelete = comment => {
      currentComment.value = comment
      deleting.value = true
    }
    const deleteComment = async () => {
      await $http.delete(`/students/${student.uuid}/comments/${currentComment.value.id}`)
    }
    const modalClosed = () => {
      fetchComments()
      editing.value = false
      deleting.value = false
      currentComment.value = {}
    }
    fetchComments()

    return {
      form,
      saveComment,
      comments,
      fetching,
      displayDate,
      user,
      currentComment,
      editComment,
      editing,
      deleting,
      modalClosed,
      promptDelete,
      deleteComment,
    }
  }
})
</script>

<style lang="postcss">
.comment-content p {
  margin-bottom: theme('spacing.4');
}

.comment-content p:last-child {
  margin-bottom: 0;
}

.comment-content a {
  @apply transition font-medium text-primary-600 dark:text-primary-500 hover:text-primary-500 dark:hover:text-primary-300 focus:outline-none;
}
</style>
