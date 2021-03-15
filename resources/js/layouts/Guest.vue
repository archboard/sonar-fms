<template>
  <div class="min-h-screen flex flex-col sm:justify-center pt-48 sm:pt-0 items-center bg-gradient-to-br from-yellow-300 via-red-400 to-primary-600 dark:from-yellow-700 dark:via-red-900 dark:to-primary-800">
    <div class="max-w-md px-4 w-full relative">
      <div class="pb-5 absolute top-0 inset-x-0 flex justify-center transform -translate-y-full">
        <inertia-link href="/">
          <Logo class="w-20 h-20 fill-current text-white" />
        </inertia-link>
      </div>
      <CardWrapper>
        <CardPadding>
          <slot />
        </CardPadding>
      </CardWrapper>
    </div>
  </div>
</template>

<script>
import Logo from '@/components/ApplicationLogo'
import CardWrapper from '@/components/CardWrapper'
import CardPadding from '@/components/CardPadding'

export default {
  components: {
    CardPadding,
    CardWrapper,
    Logo,
  }
}
</script>
