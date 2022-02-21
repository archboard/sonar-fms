<template>
  <Authenticated>
    <template v-slot:content>
      <div class="py-8 xl:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div>
            <div>
              <div>
                <div class="md:flex md:items-start md:justify-between md:space-x-4">
                  <div>
                    <h1 class="text-2xl font-bold">{{ student.full_name }}</h1>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                      {{ student.grade_level_formatted }}
                    </p>

                    <h2 class="sr-only">Details</h2>
                    <div class="space-y-5 mt-6">
                      <div v-if="student.enrolled" class="flex items-center space-x-2">
                        <CheckCircleIcon class="h-5 w-5 text-green-500" />
                        <span class="text-green-700 dark:text-green-400 text-sm font-medium">{{ __('Currently enrolled') }}</span>
                      </div>
                      <div v-else class="flex items-center space-x-2">
                        <XCircleIcon class="h-5 w-5 text-yellow-500" />
                        <span class="text-yellow-700 dark:text-yellow-400 text-sm font-medium">{{ __('Not enrolled') }}</span>
                      </div>

                      <div class="flex items-center space-x-2">
                        <CalculatorIcon class="h-5 w-5 text-gray-400" />
                        <span class="text-sm font-medium" v-if="unpaidInvoices === 1">
                          {{ __('1 unpaid invoice') }}
                        </span>
                        <span v-else class="text-sm font-medium">
                          {{ __(':number unpaid invoices', { number: unpaidInvoices }) }}
                        </span>
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
                  </div>

                  <div class="mt-4 flex items-start space-x-3 md:mt-0">
                    <Button component="inertia-link" size="sm" :href="$route('students.invoices.create', student)">
                      {{ __('New invoice') }}
                    </Button>
                  </div>
                </div>

                <div>
                  <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div class="px-4 py-5 bg-gradient-to-br from-primary-500 to-fuchsia-600 dark:from-primary-700 dark:to-fuchsia-600 shadow rounded-lg overflow-hidden sm:p-6">
                      <dt class="text-sm font-medium text-primary-100 dark:text-gray-300 truncate">
                        {{ __('Account Balance') }}
                      </dt>
                      <dd class="mt-1 text-3xl font-semibold text-white">
                        {{ displayCurrency(unpaidAmount) }}
                      </dd>
                    </div>

                    <div class="px-4 py-5 bg-gradient-to-br from-gray-500 to-gray-600 dark:from-gray-700 dark:to-gray-600 shadow rounded-lg overflow-hidden sm:p-6">
                      <dt class="text-sm font-medium text-gray-100 dark:text-gray-300 truncate">
                        {{ __('Revenue') }}
                      </dt>
                      <dd class="mt-1 text-3xl font-semibold text-white">
                        {{ displayCurrency(revenue) }}
                      </dd>
                    </div>
                  </dl>
                </div>

                <aside class="mt-8">
                  <div class="mt-6 border-t border-gray-300 dark:border-gray-600 py-6 space-y-8">
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
                          {{ __('No guardians are associated with :name. Make sure that their contacts in PowerSchool have a name and email address saved for their contact account.', { name: student.first_name }) }}
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
                          <OutlineBadge is="a" href="#" color="bg-primary-500">
                            Accessibility
                          </OutlineBadge>
                        </li>
                      </ul>
                    </div>
                  </div>
                </aside>
              </div>
            </div>

            <!-- Invoices -->
            <section aria-labelledby="invoice-table" class="mt-8 xl:mt-10">
              <div>
                <div class="divide-y divide-gray-300 dark:divide-gray-600">
                  <div class="pb-4">
                    <h2 id="invoice-table" class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Invoices') }}</h2>
                  </div>
                  <div class="pt-6">
                    <div class="max-w-none">
                      <StudentInvoiceTable
                        ref="studentTable"
                        :student="student"
                        :permissions="permissions"
                        @edit="editInvoice"
                      />
                    </div>
                  </div>
                </div>
                </div>
            </section>

            <!-- Activity feed -->
            <StudentActivity :student="student" />
          </div>
        </div>
      </div>

      <StudentInvoiceSlideout
        v-if="showSlideout"
        @close="slideoutClosed"
        :student="student"
        :invoice="selectedInvoice"
      />
    </template>
  </Authenticated>
</template>

<script>
import { defineComponent, inject, ref } from 'vue'
import { Inertia } from '@inertiajs/inertia'
import Authenticated from '@/layouts/Authenticated'
import { XCircleIcon, CheckCircleIcon } from '@heroicons/vue/outline'
import { CalculatorIcon } from '@heroicons/vue/solid'
import dayjs from 'dayjs'
import Spinner from '@/components/icons/spinner'
import OutlineBadge from '@/components/OutlineBadge'
import Button from '@/components/Button'
import Textarea from '@/components/forms/Textarea'
import StudentInvoiceSlideout from '@/components/slideouts/StudentInvoiceSlideout'
import StudentInvoiceTable from '@/components/StudentInvoiceTable'
import cloneDeep from 'lodash/cloneDeep'
import displaysCurrency from '@/composition/displaysCurrency'
import StudentActivity from '@/components/StudentActivity'

export default defineComponent({
  components: {
    StudentActivity,
    StudentInvoiceTable,
    StudentInvoiceSlideout,
    Button,
    OutlineBadge,
    Spinner,
    XCircleIcon,
    CheckCircleIcon,
    Authenticated,
    Textarea,
    CalculatorIcon,
  },

  props: {
    title: String,
    student: Object,
    user: Object,
    school: Object,
    unpaidInvoices: Number,
    permissions: Object,
    unpaidAmount: [Number, String],
    revenue: [Number, String],
  },

  setup ({ student }) {
    const $route = inject('$route')
    const enrolledAt = dayjs(student.initial_district_entry_date)
    const showSlideout = ref(false)
    const syncingGuardians = ref(false)
    const studentTable = ref(null)
    const selectedInvoice = ref({})
    const syncGuardians = () => {
      syncingGuardians.value = true

      Inertia.post($route('students.guardians.sync', student), null, {
        onFinish () {
          syncingGuardians.value = false
        }
      })
    }
    const slideoutClosed = () => {
      showSlideout.value = false
      studentTable.value.fetchInvoices()
    }
    const editInvoice = invoice => {
      selectedInvoice.value = cloneDeep(invoice)
      showSlideout.value = true
    }
    const { displayCurrency } = displaysCurrency()

    return {
      enrolledAt,
      syncingGuardians,
      syncGuardians,
      showSlideout,
      studentTable,
      slideoutClosed,
      selectedInvoice,
      editInvoice,
      displayCurrency,
    }
  }
})
</script>
