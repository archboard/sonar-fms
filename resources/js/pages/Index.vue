<template>
  <Authenticated>
    <CardWrapper>
      <CardPadding>
        <CardHeader>{{ __('Get started') }}</CardHeader>
        <ul class="mt-4" role="list">
          <li>
            <Link href="/invoices/create">{{ __('Create an invoice') }}</Link>
          </li>
          <li>
            <Link href="/invoices/imports/create">{{ __('Import invoices') }}</Link>
          </li>
        </ul>
      </CardPadding>
    </CardWrapper>
  </Authenticated>
</template>

<script>
import { defineComponent, ref } from 'vue'
import Authenticated from '../layouts/Authenticated'
import CardWrapper from '@/components/CardWrapper'
import CardPadding from '@/components/CardPadding'
import CardHeader from '@/components/CardHeader'
import Link from '@/components/Link'

export default defineComponent({
  components: {
    Link,
    CardHeader,
    CardPadding,
    CardWrapper,
    Authenticated
  },

  setup () {

  }
})
</script>
