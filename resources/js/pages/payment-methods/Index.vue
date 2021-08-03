<template>
  <Authenticated>
    <template #actions>
      <Button component="inertia-link" as="button" :href="$route('payment-methods.create')" size="sm">
        {{ __('Add method') }}
      </Button>
    </template>

    <Table>
      <Thead>
        <tr>
          <Th>{{ __('Method') }}</Th>
          <Th></Th>
        </tr>
      </Thead>
      <Tbody>
        <tr
          v-for="method in paymentMethods"
          :key="method.id"
        >
          <Td :lighter="false">
            <div class="flex items-center space-x-2">
              <span>{{ method.driver_data.label }}</span>
              <SolidBadge v-if="!method.active" size="sm" color="primary">
                {{ __('Inactive') }}
              </SolidBadge>
            </div>
          </Td>
          <Td class="text-right">
            <Link :href="$route('payment-methods.edit', method)">
              {{ __('Edit') }}
            </Link>
          </Td>
        </tr>
        <tr v-if="paymentMethods.length === 0">
          <Td colspan="2" class="text-center">
            {{ __('No payment methods are configured yet.') }} <Link :href="$route('payment-methods.create')">{{ __('Add one') }}</Link>.
          </Td>
        </tr>
      </Tbody>
    </Table>
  </Authenticated>
</template>

<script>
import { defineComponent, ref } from 'vue'
import Authenticated from '@/layouts/Authenticated'
import PageProps from '@/mixins/PageProps'
import Button from '@/components/Button'
import Table from '@/components/tables/Table'
import Thead from '@/components/tables/Thead'
import Th from '@/components/tables/Th'
import Tbody from '@/components/tables/Tbody'
import Td from '@/components/tables/Td'
import Link from '@/components/Link'
import SolidBadge from '@/components/SolidBadge'

export default defineComponent({
  mixins: [PageProps],
  components: {
    SolidBadge,
    Td,
    Tbody,
    Th,
    Thead,
    Table,
    Button,
    Authenticated,
    Link,
  },
  props: {
    paymentMethods: Array,
  },

  setup () {

  }
})
</script>
