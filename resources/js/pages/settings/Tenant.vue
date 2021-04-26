<template>
  <Authenticated>
    <div class="space-y-8 lg:space-y-10">
      <CardWrapper>
        <CardPadding>
          <form @submit.prevent="submit" data-cy="form">
            <FormMultipartWrapper>
              <Fieldset>
                <InputWrap :error="form.errors.license">
                  <Label for="license">{{ __('Product License') }}</Label>
                  <Input v-model="form.license" type="text" id="license" required />
                </InputWrap>
                <InputWrap :error="form.errors.name">
                  <Label for="name">{{ __('Tenant Name') }}</Label>
                  <Input v-model="form.name" type="text" id="name" data-cy="name" required />
                </InputWrap>
                <InputWrap :error="form.errors.ps_url">
                  <Label for="ps_url">{{ __('PowerSchool URL') }}</Label>
                  <Input v-model="form.ps_url" type="url" id="ps_url" data-cy="ps_url" />
                </InputWrap>
                <InputWrap :error="form.errors.ps_client_id">
                  <Label for="ps_client_id">{{ __('PowerSchool Client ID') }}</Label>
                  <Input v-model="form.ps_client_id" type="text" id="ps_client_id" data-cy="ps_client_id" required />
                </InputWrap>
                <InputWrap :error="form.errors.ps_secret">
                  <Label for="ps_secret">{{ __('PowerSchool Client Secret') }}</Label>
                  <Input v-model="form.ps_secret" type="text" id="ps_secret" data-cy="ps_secret" />
                </InputWrap>
                <InputWrap :error="form.errors.allow_password_auth">
                  <Label>
                    <Checkbox name="allow_password_auth" v-model:checked="form.allow_password_auth" data-cy="allow_password_auth" />
                    <CheckboxText>{{ __('Allow password authentication') }}</CheckboxText>
                    <help-text class="mt-1">{{ __('This will allow users to create and manage a password outside of PowerSchool and allow them to login directly.') }}</help-text>
                  </Label>
                </InputWrap>
                <InputWrap :error="form.errors.allow_oidc_login">
                  <Label>
                    <Checkbox name="allow_oidc_login" v-model:checked="form.allow_oidc_login" data-cy="allow_oidc_login" />
                    <CheckboxText>{{ __('Allow PowerSchool OpenID Connect authentication') }}</CheckboxText>
                    <help-text class="mt-1">{{ __('This will display a single-sign-on button for PowerSchool and only applies to version 20.11 and newer of PowerSchool.') }}</help-text>
                  </Label>
                </InputWrap>
              </Fieldset>

              <div class="pt-8">
                <div class="mb-6">
                  <CardSectionHeader>
                    {{ __('SMTP Settings') }}
                  </CardSectionHeader>
                  <HelpText class="text-sm mt-1">
                    {{ __('These are the SMTP settings so that mail can be sent from the system.') }}
                  </HelpText>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
                  <InputWrap :error="form.errors.smtp_host">
                    <Label for="smtp_host">{{ __('Host') }}</Label>
                    <Input v-model="form.smtp_host" type="text" id="smtp_host" data-cy="smtp_host" required />
                  </InputWrap>
                  <InputWrap :error="form.errors.smtp_port">
                    <Label for="smtp_port">{{ __('Port') }}</Label>
                    <Input v-model="form.smtp_port" type="number" id="smtp_port" data-cy="smtp_port" required />
                  </InputWrap>
                  <InputWrap :error="form.errors.smtp_username">
                    <Label for="smtp_username">{{ __('Username') }}</Label>
                    <Input v-model="form.smtp_username" type="text" id="smtp_username" data-cy="smtp_username" />
                  </InputWrap>
                  <InputWrap :error="form.errors.smtp_password">
                    <Label for="smtp_password">{{ __('Password') }}</Label>
                    <Input v-model="form.smtp_password" class="font-mono" type="text" id="smtp_password" data-cy="smtp_password" />
                  </InputWrap>
                  <InputWrap :error="form.errors.smtp_from_name">
                    <Label for="smtp_from_name">{{ __('From Name') }}</Label>
                    <Input v-model="form.smtp_from_name" type="text" id="smtp_from_name" data-cy="smtp_from_name" required />
                  </InputWrap>
                  <InputWrap :error="form.errors.smtp_from_address">
                    <Label for="smtp_from_address">{{ __('From Address') }}</Label>
                    <Input v-model="form.smtp_from_address" type="email" id="smtp_from_address" data-cy="smtp_from_address" required />
                  </InputWrap>
                  <InputWrap :error="form.errors.smtp_encryption">
                    <Label for="smtp_encryption">{{ __('Encryption') }}</Label>
                    <Select v-model="form.smtp_encryption" id="smtp_encryption" data-cy="smtp_encryption">
                      <option :value="null">{{ __('None') }}</option>
                      <option value="tls">TLS</option>
                      <option value="ssl">SSL</option>
                    </Select>
                  </InputWrap>
                </div>
              </div>
            </FormMultipartWrapper>
            <CardAction :negative-margin="true">
              <Button type="submit" :loading="form.processing">
                {{ __('Save') }}
              </Button>
            </CardAction>
          </form>
        </CardPadding>
      </CardWrapper>

      <CardWrapper>
        <div>
          <CardPadding>
            <div class="flex justify-between">
              <div>
                <CardSectionHeader>
                  {{ __('SIS Sync Settings') }}
                </CardSectionHeader>
                <HelpText class="text-sm mt-1">
                  {{ __('Configure the hours on which your SIS data will sync back to Sonar FMS.') }}
                </HelpText>
              </div>
              <div>
                <Button @click.prevent="syncNow" size="sm" :disabled="sisSyncing">
                  <span v-if="sisSyncing" class="mr-2">
                    <Spinner class="h-4 w-4" />
                  </span>
                  <span v-if="sisSyncing">
                    {{ __('Syncing...') }}
                  </span>
                  <span v-else>
                    {{ __('Sync Now') }}
                  </span>
                </Button>
              </div>
            </div>
          </CardPadding>
        </div>

        <CardPadding>
          <Alert v-if="sisSyncing && batch.id" class="mb-8">
            {{ __('Finished syncing :processedJobs/:totalJobs schools.', batch) }}
          </Alert>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <form @submit.prevent="addSyncTime" class="md:col-span-2">
              <InputWrap>
                <Label for="hour">{{ __('Hour') }}</Label>
                <div class="flex">
                  <div class="flex-1 pr-4">
                    <Select v-model="syncTimesForm.hour" id="hour">
                      <option :value="null">{{ __('None') }}</option>
                      <option
                        v-for="hour in hourOptions"
                        :key="hour"
                        :value="hour"
                      >
                        {{ hour }}:00
                      </option>
                    </Select>
                  </div>
                  <Button type="submit" class="flex items-center">
                    {{ __('Add') }}
                  </Button>
                </div>
                <HelpText class="mt-1">{{ __('Times are based on the UTC timezone. Right now it is :time UTC.', { time: currentTime }) }}</HelpText>
              </InputWrap>
            </form>

            <div>
              <CardSectionHeader class="text-right">
                {{ __('Configured Sync Times') }}
              </CardSectionHeader>
              <ul class="pt-4 space-y-2">
                <li
                  v-for="time in syncTimes"
                  :key="time.id"
                  class="flex items-center justify-end"
                >
                  <span>{{ time.hour }}:00</span>
                  <button @click.prevent="deleteSyncTime(time)" class="ml-2 rounded-full flex items-center justify-center h-5 w-5 focus:outline-none focus:bg-red-100 transition">
                    <TrashIcon class="h-4 w-4 text-red-500" />
                  </button>
                </li>
              </ul>
            </div>
          </div>

          <form @submit.prevent="saveSyncEmails">
            <Fieldset>
              <InputWrap :error="syncEmailsForm.errors.sync_notification_emails">
                <Label for="sync_notification_emails">{{ __('Notify these emails after a sync completes') }}</Label>
                <Input v-model="syncEmailsForm.sync_notification_emails" type="text" id="sync_notification_emails" />
                <HelpText class="mt-1">{{ __('You may separate multiple email addresses with a comma or semicolon.') }}</HelpText>
              </InputWrap>
            </Fieldset>

            <CardAction :negative-margin="true">
              <Button type="submit" :loading="syncEmailsForm.processing" />
            </CardAction>
          </form>
        </CardPadding>
      </CardWrapper>

      <CardWrapper>
        <CardPadding>
          <CardSectionHeader>
            {{ __('Active Schools') }}
          </CardSectionHeader>
          <HelpText class="text-sm mt-1">
            {{ __('Set the schools that will use Sonar FMS. Saving these settings while syncing will cause the sync to stop.') }}
          </HelpText>
        </CardPadding>

        <form @submit.prevent="schoolsForm.put($route('tenant.schools'), { preserveScroll: true })">
          <CardPadding>
            <Fieldset>
              <InputWrap
                v-for="school in schools"
                :key="school.id"
              >
                <Label class="flex items-center">
                  <Checkbox name="schools" v-model:checked="schoolsForm.schools" :value="school.id" class="mr-2" />
                  <CheckboxText>
                    <span
                      :class="{
                        'text-gray-400 dark:text-gray-500': !schoolsForm.schools.includes(school.id),
                        'text-gray-900 dark:text-gray-100': schoolsForm.schools.includes(school.id)
                      }"
                    >
                      {{ school.name }}
                    </span>
                  </CheckboxText>
                </Label>
              </InputWrap>
            </Fieldset>
          </CardPadding>

           <CardAction>
             <Button type="submit" :loading="schoolsForm.processing">
               {{ __('Save') }}
             </Button>
          </CardAction>
        </form>
      </CardWrapper>
    </div>
  </Authenticated>
</template>

<script>
import { defineComponent, ref, inject, computed, onBeforeUnmount } from 'vue'
import { useForm } from '@inertiajs/inertia-vue3'
import { Inertia } from '@inertiajs/inertia'
import Authenticated from '../../layouts/Authenticated'
import Fieldset from '../../components/forms/Fieldset'
import InputWrap from '../../components/forms/InputWrap'
import Label from '../../components/forms/Label'
import Input from '../../components/forms/Input'
import Button from '../../components/Button'
import Checkbox from '../../components/forms/Checkbox'
import CheckboxText from '../../components/forms/CheckboxText'
import CardWrapper from '../../components/CardWrapper'
import CardPadding from '../../components/CardPadding'
import CardAction from '../../components/CardAction'
import CardSectionHeader from '../../components/CardSectionHeader'
import HelpText from '../../components/HelpText'
import Select from '../../components/forms/Select'
import FormMultipartWrapper from '../../components/forms/FormMultipartWrapper'
import range from 'lodash/range'
import { TrashIcon } from '@heroicons/vue/outline'
import Alert from '../../components/Alert'
import Spinner from '../../components/icons/spinner'
import dayjs from '@/plugins/dayjs'

export default defineComponent({
  components: {
    Spinner,
    Alert,
    TrashIcon,
    FormMultipartWrapper,
    Select,
    HelpText,
    CardSectionHeader,
    CardAction,
    CardPadding,
    CardWrapper,
    CheckboxText,
    Checkbox,
    Button,
    Input,
    Label,
    InputWrap,
    Fieldset,
    Authenticated
  },

  props: {
    tenant: Object,
    syncTimes: Array,
    schools: Array,
  },

  setup (props) {
    const $route = inject('$route')
    const $http = inject('$http')

    // General settings
    const form = useForm({
      ...props.tenant
    })
    const submit = () => {
      form.post($route('settings.tenant'))
    }

    // Active schools
    const schoolsForm = useForm({
      schools: props.schools.filter(s => s.active).map(s => s.id)
    })

    // Sync times
    const syncEmailsForm = useForm({
      sync_notification_emails: props.tenant.sync_notification_emails
    })
    const syncTimesForm = useForm({
      hour: null
    })
    const saveSyncEmails = () => {
      syncEmailsForm.put($route('sis.sync.emails'), { preserveScroll: true })
    }
    const addSyncTime = () => {
      syncTimesForm.post($route('sync-times.store'), {
        preserveScroll: true,
        onFinish () {
          syncTimesForm.reset()
        }
      })
    }
    const sisSyncing = ref(props.tenant.is_syncing)
    const polling = ref(false)
    const batch = ref({})

    let syncCheckInterval
    const startSyncPoll = () => {
      console.log('starting poll')
      clearInterval(syncCheckInterval)
      $http.get($route('sis.sync.batch')).then(res => {
        batch.value = res.data
      })
      polling.value = true
      sisSyncing.value = true

      syncCheckInterval = setInterval(() => {
        if (sisSyncing.value) {
          $http.get($route('sis.sync.batch')).then(res => {
            batch.value = res.data
            console.log('batch', res.data)

            if (
              !res.data ||
              typeof res.data.progress === 'undefined' ||
              res.data.progress >= 100
            ) {
              polling.value = false
              sisSyncing.value = false
              clearInterval(syncCheckInterval)
            }
          })
        }
      }, 5000)
    }
    const deleteSyncTime = time => {
      Inertia.delete($route('sync-times.destroy', time), {
        preserveScroll: true,
      })
    }
    const syncNow = () => {
      Inertia.post($route('sis.sync'), null, {
        preserveScroll: true,
        onFinish () {
          if (!polling.value) {
            startSyncPoll()
          }
        }
      })
    }
    const hourOptions = computed(() => {
      return range(24).filter(h => !props.syncTimes.some(s => s.hour === h))
    })

    // Start the batch poll
    if (!polling.value && sisSyncing.value) {
      startSyncPoll()
    }

    // For displaying the time
    const getTime = () => dayjs().tz('UTC').format('H:mm')
    const currentTime = ref(getTime())
    const timeInterval = setInterval(() => {
      currentTime.value = getTime()
    }, 2000)

    onBeforeUnmount(() => {
      clearInterval(timeInterval)
      clearInterval(syncCheckInterval)
    })

    return {
      form,
      submit,
      syncTimesForm,
      syncEmailsForm,
      saveSyncEmails,
      addSyncTime,
      deleteSyncTime,
      syncNow,
      hourOptions,
      schoolsForm,
      currentTime,
      sisSyncing,
      batch,
    }
  },
})
</script>
