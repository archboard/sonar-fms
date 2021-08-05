<template>
  <div class="error-bg min-h-screen w-full flex items-center justify-center">
    <div class="max-w-lg w-full mx-auto px-4">
      <CardWrapper>
        <CardPadding class="space-y-4 text-center">
          <h1 class="font-medium text-2xl">{{ title }}</h1>
          <div>{{ description }}</div>
          <div class="space-y-4 md:flex md:space-x-5 md:space-y-0">
            <Button component="inertia-link" href="/" is-block>
              {{ __('Home') }}
            </Button>
            <Button component="a" href="https://www.archboard.io/products/sonar-fms/" is-block target="_blank">
              {{ __('About Sonar FMS') }}
            </Button>
          </div>
        </CardPadding>
      </CardWrapper>
    </div>
  </div>
</template>

<script>
import PageProps from '@/mixins/PageProps'
import setsTitle from '@/composition/setsTitle'
import CardWrapper from '@/components/CardWrapper'
import CardPadding from '@/components/CardPadding'
import Button from '@/components/Button'

export default {
  components: {Button, CardPadding, CardWrapper},
  mixins: [PageProps],
  props: {
    status: Number,
  },

  setup () {
    setsTitle()
  },

  computed: {
    title () {
      return {
        503: this.__('503: Service Unavailable'),
        500: this.__('500: Server Error'),
        404: this.__('404: Page Not Found'),
        403: this.__('403: Forbidden'),
      }[this.status] || 'Error'
    },

    description () {
      return {
        503: this.__('Sorry, we are doing some maintenance. Please check back soon.'),
        500: this.__('Whoops, something went wrong on our servers.'),
        404: this.__('Sorry, the page you are looking for could not be found.'),
        403: this.__('Sorry, you are forbidden from accessing this page.'),
      }[this.status]
    },
  },
}
</script>
