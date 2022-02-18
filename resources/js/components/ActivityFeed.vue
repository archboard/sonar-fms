<template>
  <section aria-labelledby="activity-title" class="mt-8 xl:mt-10">
    <div>
      <div class="divide-y divide-gray-300 dark:divide-gray-600">
        <div class="pb-4">
          <h2 id="activity-title" class="text-lg font-medium">{{ __('Activity') }}</h2>
        </div>
        <div class="pt-6">
          <!-- Activity feed-->
          <div class="flow-root">
            <ul class="-mb-8">
  <!--                    <li>-->
  <!--                      <div class="relative pb-8">-->
  <!--                        <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-300" aria-hidden="true"></span>-->
  <!--                        <div class="relative flex items-start space-x-3">-->
  <!--                          <div class="relative">-->
  <!--                            <img class="h-10 w-10 rounded-full bg-gray-400 flex items-center justify-center ring-8 ring-gray-100 dark:ring-gray-900" src="https://images.unsplash.com/photo-1520785643438-5bf77931f493?ixlib=rb-=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=8&w=256&h=256&q=80" alt="">-->

  <!--                            <span class="absolute -bottom-0.5 -right-1 bg-gray-100 dark:bg-gray-900 rounded-tl px-0.5 py-px">-->
  <!--                              &lt;!&ndash; Heroicon name: solid/chat-alt &ndash;&gt;-->
  <!--                              <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">-->
  <!--                                <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zM7 8H5v2h2V8zm2 0h2v2H9V8zm6 0h-2v2h2V8z" clip-rule="evenodd" />-->
  <!--                              </svg>-->
  <!--                            </span>-->
  <!--                          </div>-->
  <!--                          <div class="min-w-0 flex-1">-->
  <!--                            <div>-->
  <!--                              <div class="text-sm">-->
  <!--                                <a href="#" class="font-medium text-gray-900 dark:text-gray-100">Eduardo Benz</a>-->
  <!--                              </div>-->
  <!--                              <p class="mt-0.5 text-sm text-gray-500">-->
  <!--                                Commented 6d ago-->
  <!--                              </p>-->
  <!--                            </div>-->
  <!--                            <div class="mt-2 text-sm text-gray-700 dark:text-gray-300">-->
  <!--                              <p>-->
  <!--                                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Tincidunt nunc ipsum tempor purus vitae id. Morbi in vestibulum nec varius. Et diam cursus quis sed purus nam.-->
  <!--                              </p>-->
  <!--                            </div>-->
  <!--                          </div>-->
  <!--                        </div>-->
  <!--                      </div>-->
  <!--                    </li>-->

              <li
                v-for="(activity, index) in activities"
                :key="activity.id"
              >
                <div class="relative pb-8">
                  <span v-if="index !== activities.length - 1" class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-300" aria-hidden="true"></span>
                  <div class="relative flex items-start space-x-3">
                    <div>
                      <div class="relative px-1">
                        <div class="h-8 w-8 bg-gray-300 dark:bg-gray-800 rounded-full ring-8 ring-gray-100 dark:ring-gray-900 flex items-center justify-center">
                          <component :is="activity.component" class="h-5 w-5 text-gray-500 dark:text-gray-400" />
                        </div>
                      </div>
                    </div>
                    <div class="min-w-0 flex-1 py-1.5">
                      <div class="text-sm text-gray-900 dark:text-gray-100">
                        {{ activity.description }} <span class="text-gray-500 whitespace-nowrap">{{ fromNow(activity.created_at) }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </li>

  <!--                    <li>-->
  <!--                      <div class="relative pb-8">-->
  <!--                        <div class="relative flex items-start space-x-3">-->
  <!--                          <div class="relative">-->
  <!--                            <img class="h-10 w-10 rounded-full bg-gray-400 flex items-center justify-center ring-8 ring-gray-100 dark:ring-gray-900" src="https://images.unsplash.com/photo-1531427186611-ecfd6d936c79?ixlib=rb-=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=8&w=256&h=256&q=80" alt="">-->

  <!--                            <span class="absolute -bottom-0.5 -right-1 bg-gray-100 dark:bg-gray-900 rounded-tl px-0.5 py-px">-->
  <!--                              &lt;!&ndash; Heroicon name: solid/chat-alt &ndash;&gt;-->
  <!--                              <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">-->
  <!--                                <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zM7 8H5v2h2V8zm2 0h2v2H9V8zm6 0h-2v2h2V8z" clip-rule="evenodd" />-->
  <!--                              </svg>-->
  <!--                            </span>-->
  <!--                          </div>-->
  <!--                          <div class="min-w-0 flex-1">-->
  <!--                            <div>-->
  <!--                              <div class="text-sm">-->
  <!--                                <a href="#" class="font-medium text-gray-900 dark:text-gray-100">Jason Meyers</a>-->
  <!--                              </div>-->
  <!--                              <p class="mt-0.5 text-sm text-gray-500">-->
  <!--                                Commented 2h ago-->
  <!--                              </p>-->
  <!--                            </div>-->
  <!--                            <div class="mt-2 text-sm text-gray-700 dark:text-gray-300">-->
  <!--                              <p>-->
  <!--                                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Tincidunt nunc ipsum tempor purus vitae id. Morbi in vestibulum nec varius. Et diam cursus quis sed purus nam. Scelerisque amet elit non sit ut tincidunt condimentum. Nisl ultrices eu venenatis diam.-->
  <!--                              </p>-->
  <!--                            </div>-->
  <!--                          </div>-->
  <!--                        </div>-->
  <!--                      </div>-->
  <!--                    </li>-->
            </ul>
          </div>
  <!--                <div class="mt-6">-->
  <!--                  <div class="flex space-x-3">-->
  <!--                    <div class="flex-shrink-0">-->
  <!--                      <div class="relative">-->
  <!--                        <img class="h-10 w-10 rounded-full bg-gray-400 flex items-center justify-center ring-8 ring-gray-100 dark:ring-gray-900" src="https://images.unsplash.com/photo-1517365830460-955ce3ccd263?ixlib=rb-=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=8&w=256&h=256&q=80" alt="">-->

  <!--                        <span class="absolute -bottom-0.5 -right-1 bg-gray-100 dark:bg-gray-900 rounded-tl px-0.5 py-px">-->
  <!--                          &lt;!&ndash; Heroicon name: solid/chat-alt &ndash;&gt;-->
  <!--                          <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">-->
  <!--                            <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zM7 8H5v2h2V8zm2 0h2v2H9V8zm6 0h-2v2h2V8z" clip-rule="evenodd" />-->
  <!--                          </svg>-->
  <!--                        </span>-->
  <!--                      </div>-->
  <!--                    </div>-->
  <!--                    <div class="min-w-0 flex-1">-->
  <!--                      <form action="#">-->
  <!--                        <div>-->
  <!--                          <label for="comment" class="sr-only">Comment</label>-->
  <!--                          <Textarea id="comment" name="comment" rows="3" placeholder="Leave a comment"></Textarea>-->
  <!--                        </div>-->
  <!--                        <div class="mt-6 flex items-center justify-end space-x-4">-->
  <!--                          <button type="button" class="inline-flex justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-gray-100 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900">-->
  <!--                            &lt;!&ndash; Heroicon name: solid/check-circle &ndash;&gt;-->
  <!--                            <svg class="-ml-1 mr-2 h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">-->
  <!--                              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />-->
  <!--                            </svg>-->
  <!--                            <span>Close issue</span>-->
  <!--                          </button>-->
  <!--                          <Button class="text-sm">-->
  <!--                            Comment-->
  <!--                          </Button>-->
  <!--                        </div>-->
  <!--                      </form>-->
  <!--                    </div>-->
  <!--                  </div>-->
  <!--                </div>-->
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import { defineComponent } from 'vue'
import displaysDate from '@/composition/displaysDate'
import { BellIcon, PencilIcon, ReceiptRefundIcon, CashIcon } from '@heroicons/vue/solid'

export default defineComponent({
  components: {
    BellIcon,
    PencilIcon,
    ReceiptRefundIcon,
    CashIcon,
  },
  props: {
    activities: {
      type: Array,
      required: true,
    },
  },

  setup (props, context) {
    const { displayDate, fromNow } = displaysDate()

    return {
      displayDate,
      fromNow,
    }
  },
})
</script>
