<template>
  <Authenticated>
    <template v-slot:content>
      <div class="py-8 xl:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 xl:grid xl:grid-cols-3">
          <div class="xl:col-span-2 xl:pr-8 xl:border-r xl:border-gray-300 xl:dark:border-gray-600">
            <div>
              <div>
                <div class="md:flex md:items-start md:justify-between md:space-x-4 xl:border-b xl:dark:border-gray-600 xl:pb-6">
                  <div>
                    <h1 class="text-2xl font-bold">{{ student.full_name }}</h1>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                      #400 opened by
                      <a href="#" class="font-medium text-gray-900 dark:text-gray-100">Hilary Mahy</a>
                      in
                      <a href="#" class="font-medium text-gray-900 dark:text-gray-100">Customer Portal</a>
                    </p>
                  </div>
                  <div class="mt-4 flex items-start space-x-3 md:mt-0">
                    <Button color="white">
                      <!-- Heroicon name: solid/pencil -->
                      <svg class="-ml-1 mr-2 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                      </svg>
                      <span class="text-sm">Edit</span>
                    </Button>
                    <Button color="white" size="sm" @click.prevent="createInvoice = true">
                      {{ __('New invoice') }}
                    </Button>
                  </div>
                </div>

                <!-- Mobile details -->
                <aside class="mt-8 xl:hidden">
                  <h2 class="sr-only">Details</h2>
                  <div class="space-y-5">
                    <div v-if="student.enrolled" class="flex items-center space-x-2">
                      <CheckCircleIcon class="h-5 w-5 text-green-500" />
                      <span class="text-green-700 dark:text-green-400 text-sm font-medium">{{ __('Currently enrolled') }}</span>
                    </div>
                    <div v-else class="flex items-center space-x-2">
                      <XCircleIcon class="h-5 w-5 text-yellow-500" />
                      <span class="text-yellow-700 dark:text-yellow-400 text-sm font-medium">{{ __('Not enrolled') }}</span>
                    </div>

                    <div class="flex items-center space-x-2">
                      <!-- Heroicon name: solid/chat-alt -->
                      <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zM7 8H5v2h2V8zm2 0h2v2H9V8zm6 0h-2v2h2V8z" clip-rule="evenodd" />
                      </svg>
                      <span class="text-gray-900 dark:text-gray-100 text-sm font-medium">4 comments</span>
                    </div>

                    <div class="flex items-center space-x-2">
                      <!-- Heroicon name: solid/calendar -->
                      <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                      </svg>
                      <span class="text-sm font-medium">
                        {{ __('Entered district on :date', { date: enrolledAt.format('MMM D, YYYY') }) }}
                      </span>
                    </div>
                  </div>
                  <div class="mt-6 border-t border-b border-gray-300 dark:border-gray-600 py-6 space-y-8">
                    <div>
                      <h2 class="text-sm font-medium text-gray-500 dark:text-gray-300 flex">
                        <span>
                          {{ __('Guardians') }}
                        </span>
                        <button
                          class="ml-3 font-normal focus:outline-none relative inline-flex items-center justify-center"
                          @click.prevent="syncGuardians"
                          :class="{
                            'w-8': syncingGuardians
                          }"
                        >
                          <span v-if="syncingGuardians" class="px-2 absolute right-0 top-[2px] inline-flex">
                            <Spinner class="w-4 h-4" />
                          </span>
                          <span v-else>
                            {{ __('Sync') }}
                          </span>
                        </button>
                      </h2>
                      <ul class="mt-3 space-y-3">
                        <li
                          v-for="guardian in student.users"
                          :key="guardian.id"
                        >
                          <a href="#" class="text-sm font-medium text-gray-900 dark:text-gray-100 hover:underline">
                            {{ guardian.full_name }}
                          </a>
                        </li>
                        <li v-if="student.users.length === 0" class="text-sm">
                          {{ __('No guardians associated with :name', { name: student.first_name }) }}
                        </li>
                      </ul>
                    </div>
                    <div>
                      <h2 class="text-sm font-medium text-gray-500 dark:text-gray-300">Tags</h2>
                      <ul class="mt-2 leading-8 space-x-2">
                        <li class="inline">
                          <OutlineBadge is="a" href="#" color="bg-rose-500">
                            Bug
                          </OutlineBadge>
                        </li>
                        <li class="inline">
                          <OutlineBadge is="a" href="#" color="bg-indigo-500">
                            Accessibility
                          </OutlineBadge>
                        </li>
                      </ul>
                    </div>
                  </div>
                </aside>

                <div class="py-3 xl:pt-6 xl:pb-0">
                  <h2 class="sr-only">Description</h2>
                  <div class="prose dark:text-gray-100 max-w-none">
                    <p>
                      Lorem ipsum dolor sit amet consectetur adipisicing elit. Expedita, hic? Commodi cumque similique id tempora molestiae deserunt at suscipit, dolor voluptatem, numquam, harum consequatur laboriosam voluptas tempore aut voluptatum alias?
                    </p>
                    <ul>
                      <li>
                        Tempor ultrices proin nunc fames nunc ut auctor vitae sed. Eget massa parturient vulputate fermentum id facilisis nam pharetra. Aliquet leo tellus.
                      </li>
                      <li>
                        Turpis ac nunc adipiscing adipiscing metus tincidunt senectus tellus.
                      </li>
                      <li>
                        Semper interdum porta sit tincidunt. Dui suspendisse scelerisque amet metus eget sed. Ut tellus in sed dignissim.
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>

            <section aria-labelledby="activity-title" class="mt-8 xl:mt-10">
              <div>
                <div class="divide-y divide-gray-300 dark:divide-gray-600">
                  <div class="pb-4">
                    <h2 id="activity-title" class="text-lg font-medium text-gray-900 dark:text-gray-100">Activity</h2>
                  </div>
                  <div class="pt-6">
                    <!-- Activity feed-->
                    <div class="flow-root">
                      <ul class="-mb-8">
                        <li>
                          <div class="relative pb-8">
                            <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-300" aria-hidden="true"></span>
                            <div class="relative flex items-start space-x-3">
                              <div class="relative">
                                <img class="h-10 w-10 rounded-full bg-gray-400 flex items-center justify-center ring-8 ring-gray-100 dark:ring-gray-900" src="https://images.unsplash.com/photo-1520785643438-5bf77931f493?ixlib=rb-=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=8&w=256&h=256&q=80" alt="">

                                <span class="absolute -bottom-0.5 -right-1 bg-gray-100 dark:bg-gray-900 rounded-tl px-0.5 py-px">
                                  <!-- Heroicon name: solid/chat-alt -->
                                  <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zM7 8H5v2h2V8zm2 0h2v2H9V8zm6 0h-2v2h2V8z" clip-rule="evenodd" />
                                  </svg>
                                </span>
                              </div>
                              <div class="min-w-0 flex-1">
                                <div>
                                  <div class="text-sm">
                                    <a href="#" class="font-medium text-gray-900 dark:text-gray-100">Eduardo Benz</a>
                                  </div>
                                  <p class="mt-0.5 text-sm text-gray-500">
                                    Commented 6d ago
                                  </p>
                                </div>
                                <div class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                                  <p>
                                    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Tincidunt nunc ipsum tempor purus vitae id. Morbi in vestibulum nec varius. Et diam cursus quis sed purus nam.
                                  </p>
                                </div>
                              </div>
                            </div>
                          </div>
                        </li>

                        <li>
                          <div class="relative pb-8">
                            <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-300" aria-hidden="true"></span>
                            <div class="relative flex items-start space-x-3">
                              <div>
                                <div class="relative px-1">
                                  <div class="h-8 w-8 bg-gray-100 rounded-full ring-8 ring-gray-100 dark:ring-gray-900 flex items-center justify-center">
                                    <!-- Heroicon name: solid/user-circle -->
                                    <svg class="h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd" />
                                    </svg>
                                  </div>
                                </div>
                              </div>
                              <div class="min-w-0 flex-1 py-1.5">
                                <div class="text-sm text-gray-500">
                                  <a href="#" class="font-medium text-gray-900 dark:text-gray-100">Hilary Mahy</a>
                                  assigned
                                  <a href="#" class="font-medium text-gray-900 dark:text-gray-100">Kristin Watson</a>
                                  <span class="whitespace-nowrap">2d ago</span>
                                </div>
                              </div>
                            </div>
                          </div>
                        </li>

                        <li>
                          <div class="relative pb-8">
                            <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-300" aria-hidden="true"></span>
                            <div class="relative flex items-start space-x-3">
                              <div>
                                <div class="relative px-1">
                                  <div class="h-8 w-8 bg-gray-100 rounded-full ring-8 ring-gray-100 dark:ring-gray-900 flex items-center justify-center">
                                    <!-- Heroicon name: solid/tag -->
                                    <svg class="h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                      <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                    </svg>
                                  </div>
                                </div>
                              </div>
                              <div class="min-w-0 flex-1 py-0">
                                <div class="text-sm leading-8 text-gray-500">
                                  <span class="mr-0.5">
                                    <a href="#" class="font-medium text-gray-900 dark:text-gray-100">Hilary Mahy</a>
                                    added tags
                                  </span>
                                  <span class="mr-0.5 space-x-2">
                                    <OutlineBadge is="a" href="#" color="bg-rose-500">
                                      Bug
                                    </OutlineBadge>
                                    <OutlineBadge is="a" href="#" color="bg-indigo-500">
                                      Accessibility
                                    </OutlineBadge>
                                  </span>
                                  <span class="whitespace-nowrap">6h ago</span>
                                </div>
                              </div>
                            </div>
                          </div>
                        </li>

                        <li>
                          <div class="relative pb-8">
                            <div class="relative flex items-start space-x-3">
                              <div class="relative">
                                <img class="h-10 w-10 rounded-full bg-gray-400 flex items-center justify-center ring-8 ring-gray-100 dark:ring-gray-900" src="https://images.unsplash.com/photo-1531427186611-ecfd6d936c79?ixlib=rb-=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=8&w=256&h=256&q=80" alt="">

                                <span class="absolute -bottom-0.5 -right-1 bg-gray-100 dark:bg-gray-900 rounded-tl px-0.5 py-px">
                                  <!-- Heroicon name: solid/chat-alt -->
                                  <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zM7 8H5v2h2V8zm2 0h2v2H9V8zm6 0h-2v2h2V8z" clip-rule="evenodd" />
                                  </svg>
                                </span>
                              </div>
                              <div class="min-w-0 flex-1">
                                <div>
                                  <div class="text-sm">
                                    <a href="#" class="font-medium text-gray-900 dark:text-gray-100">Jason Meyers</a>
                                  </div>
                                  <p class="mt-0.5 text-sm text-gray-500">
                                    Commented 2h ago
                                  </p>
                                </div>
                                <div class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                                  <p>
                                    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Tincidunt nunc ipsum tempor purus vitae id. Morbi in vestibulum nec varius. Et diam cursus quis sed purus nam. Scelerisque amet elit non sit ut tincidunt condimentum. Nisl ultrices eu venenatis diam.
                                  </p>
                                </div>
                              </div>
                            </div>
                          </div>
                        </li>
                      </ul>
                    </div>
                    <div class="mt-6">
                      <div class="flex space-x-3">
                        <div class="flex-shrink-0">
                          <div class="relative">
                            <img class="h-10 w-10 rounded-full bg-gray-400 flex items-center justify-center ring-8 ring-gray-100 dark:ring-gray-900" src="https://images.unsplash.com/photo-1517365830460-955ce3ccd263?ixlib=rb-=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=8&w=256&h=256&q=80" alt="">

                            <span class="absolute -bottom-0.5 -right-1 bg-gray-100 dark:bg-gray-900 rounded-tl px-0.5 py-px">
                              <!-- Heroicon name: solid/chat-alt -->
                              <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zM7 8H5v2h2V8zm2 0h2v2H9V8zm6 0h-2v2h2V8z" clip-rule="evenodd" />
                              </svg>
                            </span>
                          </div>
                        </div>
                        <div class="min-w-0 flex-1">
                          <form action="#">
                            <div>
                              <label for="comment" class="sr-only">Comment</label>
                              <Textarea id="comment" name="comment" rows="3" placeholder="Leave a comment"></Textarea>
                            </div>
                            <div class="mt-6 flex items-center justify-end space-x-4">
                              <button type="button" class="inline-flex justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-gray-100 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900">
                                <!-- Heroicon name: solid/check-circle -->
                                <svg class="-ml-1 mr-2 h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>Close issue</span>
                              </button>
                              <Button class="text-sm">
                                Comment
                              </Button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </section>
          </div>

          <!-- Desktop details -->
          <aside class="hidden xl:block xl:pl-8">
            <h2 class="sr-only">Details</h2>
            <div class="space-y-5">
              <div v-if="student.enrolled" class="flex items-center space-x-2">
                <CheckCircleIcon class="h-5 w-5 text-green-500" />
                <span class="text-green-700 dark:text-green-400 text-sm font-medium">{{ __('Currently enrolled') }}</span>
              </div>
              <div v-else class="flex items-center space-x-2">
                <XCircleIcon class="h-5 w-5 text-yellow-500" />
                <span class="text-yellow-700 dark:text-yellow-400 text-sm font-medium">{{ __('Not enrolled') }}</span>
              </div>
              <div class="flex items-center space-x-2">
                <!-- Heroicon name: solid/chat-alt -->
                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zM7 8H5v2h2V8zm2 0h2v2H9V8zm6 0h-2v2h2V8z" clip-rule="evenodd" />
                </svg>
                <span class="text-sm font-medium">4 comments</span>
              </div>
              <div class="flex items-center space-x-2">
                <!-- Heroicon name: solid/calendar -->
                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                </svg>
                <span class="text-sm font-medium">
                  {{ __('Entered district on :date', { date: enrolledAt.format('MMM D, YYYY') }) }}
                </span>
              </div>
            </div>
            <div class="mt-6 border-t border-gray-300 dark:border-gray-600 py-6 space-y-8">
              <div>
                <h2 class="text-sm font-medium text-gray-500 dark:text-gray-300 flex justify-between relative">
                  <span>
                    {{ __('Guardians') }}
                  </span>
                  <button class="font-normal focus:outline-none" @click.prevent="syncGuardians">
                    <span v-if="syncingGuardians" class="px-2 absolute right-0 top-0">
                      <Spinner class="w-5 h-5" />
                    </span>
                    <span v-else>
                      {{ __('Sync') }}
                    </span>
                  </button>
                </h2>
                <ul class="mt-3 space-y-3">
                  <li
                    v-for="guardian in student.users"
                    :key="guardian.id"
                  >
                    <a href="#" class="text-sm font-medium hover:underline">
                      {{ guardian.full_name }}
                    </a>
                  </li>
                </ul>
              </div>
              <div>
                <h2 class="text-sm font-medium text-gray-500 dark:text-gray-300">Tags</h2>
                <ul class="mt-2 leading-8 space-x-1">
                  <li class="inline">
                    <OutlineBadge is="a" href="#" color="bg-rose-500">
                      Bug
                    </OutlineBadge>
                  </li>
                  <li class="inline">
                    <OutlineBadge is="a" href="#" color="bg-indigo-500">
                      Accessibility
                    </OutlineBadge>
                  </li>
                </ul>
              </div>
            </div>
          </aside>
        </div>
      </div>

      <StudentInvoiceSlideout
        v-if="createInvoice"
        @close="createInvoice = false"
        :student="student"
      />
    </template>
  </Authenticated>
</template>

<script>
import { defineComponent, inject, ref } from 'vue'
import { Inertia } from '@inertiajs/inertia'
import Authenticated from '../../layouts/Authenticated'
import { XCircleIcon, CheckCircleIcon } from '@heroicons/vue/outline'
import dayjs from 'dayjs'
import Spinner from '../../components/icons/spinner'
import OutlineBadge from '../../components/OutlineBadge'
import Button from '../../components/Button'
import Textarea from '../../components/forms/Textarea'
import StudentInvoiceSlideout from '../../components/slideouts/StudentInvoiceSlideout'

export default defineComponent({
  components: {
    StudentInvoiceSlideout,
    Button,
    OutlineBadge,
    Spinner,
    XCircleIcon,
    CheckCircleIcon,
    Authenticated,
    Textarea,
  },

  props: {
    title: String,
    student: Object,
    user: Object,
    school: Object,
  },

  setup ({ student }) {
    const $route = inject('$route')
    const enrolledAt = dayjs(student.initial_district_entry_date)
    const createInvoice = ref(false)
    const syncingGuardians = ref(false)
    const syncGuardians = () => {
      syncingGuardians.value = true

      Inertia.post($route('students.guardians.sync', student), null, {
        onFinish () {
          syncingGuardians.value = false
        }
      })
    }

    return {
      enrolledAt,
      syncingGuardians,
      syncGuardians,
      createInvoice,
    }
  }
})
</script>
