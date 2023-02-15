<template>
  <Authenticated>
    <CardWrapper>
      <CardPadding>
        <form @submit.prevent="submit" data-cy="form">
          <Fieldset>
            <InputWrap :error="form.errors.first_name">
              <Label for="first_name" required>{{ __('First Name') }}</Label>
              <Input v-model="form.first_name" type="text" id="first_name" data-cy="first_name" required />
            </InputWrap>
            <InputWrap :error="form.errors.last_name">
              <Label for="last_name" required>{{ __('Last Name') }}</Label>
              <Input v-model="form.last_name" type="text" id="last_name" data-cy="last_name" required />
            </InputWrap>
            <InputWrap :error="form.errors.email">
              <Label for="email" required>{{ __('Email') }}</Label>
              <Input v-model="form.email" type="email" id="email" data-cy="email" required />
            </InputWrap>
            <InputWrap :error="form.errors.timezone">
              <Label for="timezone" required>{{ __('Timezone') }}</Label>
              <Timezone v-model="form.timezone" id="timezone" data-cy="timezone" required />
            </InputWrap>
            <InputWrap :error="form.errors.time_format">
              <Label for="time_format" required>{{ __('Time format') }}</Label>
              <Select v-model="form.time_format" id="time_format">
                <option value="12">{{ __('12 hour (:now)', { now: displayDate(realtimeNow, timeFormats['12']) }) }}</option>
                <option value="24">{{ __('24 hour (:now)', { now: displayDate(realtimeNow, timeFormats['24']) }) }}</option>
              </Select>
              <HelpText>{{ __('Time not correct? Make sure your timezone is set to your local timezone to ensure the accurate time displays in Sonar FMS.') }}</HelpText>
            </InputWrap>
            <InputWrap v-if="tenant.allow_password_auth" :error="form.errors.password">
              <Label for="password">{{ __('Password') }}</Label>
              <Input v-model="form.password" type="password" id="password" data-cy="password" />
              <HelpText class="mt-1 ml-1">{{ __('Leave empty to keep your current password.') }}</HelpText>
            </InputWrap>
            <InputWrap v-if="tenant.allow_password_auth" :error="form.errors.password_confirmation">
              <Label for="password_confirmation">{{ __('Confirm Password') }}</Label>
              <Input v-model="form.password_confirmation" type="password" id="password_confirmation" data-cy="password_confirmation" />
            </InputWrap>
          </Fieldset>
          <CardAction :negative-margin="true">
            <Button type="submit" :loading="form.processing">
              {{ __('Save') }}
            </Button>
          </CardAction>
        </form>
      </CardPadding>
    </CardWrapper>
  </Authenticated>
</template>

<script>
import { defineComponent, inject, onUnmounted, ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import Authenticated from '@/layouts/Authenticated.vue'
import pick from 'lodash/pick'
import Fieldset from '@/components/forms/Fieldset.vue'
import InputWrap from '@/components/forms/InputWrap.vue'
import Label from '@/components/forms/Label.vue'
import Input from '@/components/forms/Input.vue'
import Button from '@/components/Button.vue'
import CardWrapper from '@/components/CardWrapper.vue'
import CardPadding from '@/components/CardPadding.vue'
import HelpText from '@/components/HelpText.vue'
import CardAction from '@/components/CardAction.vue'
import Timezone from '@/components/forms/Timezone.vue'
import PageProps from '@/mixins/PageProps'
import Select from '@/components/forms/Select.vue'
import displaysDate from '@/composition/displaysDate.js'

export default defineComponent({
  mixins: [PageProps],
  components: {
    Select,
    Timezone,
    CardAction,
    HelpText,
    CardPadding,
    CardWrapper,
    Button,
    Input,
    Label,
    InputWrap,
    Fieldset,
    Authenticated
  },

  props: {
    user: Object,
  },

  setup ({ user }) {
    const $route = inject('$route')
    const form = useForm({
      ...pick(user, ['first_name', 'last_name', 'email', 'timezone', 'time_format']),
      password: '',
      password_confirmation: '',
    })
    const submit = () => {
      form.post($route('settings.personal'), {
        onSuccess () {
          form.reset('password', 'password_confirmation')
        }
      })
    }
    const { displayDate, timeFormats, getDate } = displaysDate()
    const realtimeNow = ref(getDate())
    const interval = setInterval(() => {
      realtimeNow.value = getDate()
    }, 1000)

    onUnmounted(() => {
      clearInterval(interval)
    })

    return {
      form,
      submit,
      displayDate,
      timeFormats,
      realtimeNow,
    }
  },
})
</script>
