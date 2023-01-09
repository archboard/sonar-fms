<template>
  <Authenticated>
    <Table>
      <Thead>
        <tr>
          <Th>{{ __('Method') }}</Th>
          <Th>{{ __('Description') }}</Th>
          <Th>{{ __('For import') }}</Th>
          <Th></Th>
        </tr>
      </Thead>
      <Tbody>
        <tr
          v-for="method in paymentMethods"
          :key="method.key"
        >
          <Td class="align-top" :lighter="false">
            <div class="flex items-center space-x-2 whitespace-nowrap">
              <span>{{ method.label }}</span>
              <SolidBadge v-if="!method.payment_method.id" size="sm" color="yellow">
                {{ __('Not configured') }}
              </SolidBadge>
              <SolidBadge v-else-if="!method.payment_method.active" size="sm" color="primary">
                {{ __('Inactive') }}
              </SolidBadge>
            </div>
          </Td>
          <Td>{{ method.description }}</Td>
          <Td>{{ method.detects_list }}</Td>
          <Td class="align-top text-right whitespace-nowrap">
            <Link v-if="method.payment_method.id" :href="$route('payment-methods.edit', method.payment_method)">
              {{ __('Edit') }}
            </Link>
            <Link v-else :href="$route('payment-methods.create', { driver: method.key })">
              {{ __('Set up') }}
            </Link>
          </Td>
        </tr>
      </Tbody>
    </Table>
  </Authenticated>
</template>

<script>
import { defineComponent, ref } from 'vue'
import Authenticated from '@/layouts/Authenticated.vue'
import PageProps from '@/mixins/PageProps'
import Button from '@/components/Button.vue'
import Table from '@/components/tables/Table.vue'
import Thead from '@/components/tables/Thead.vue'
import Th from '@/components/tables/Th.vue'
import Tbody from '@/components/tables/Tbody.vue'
import Td from '@/components/tables/Td.vue'
import Link from '@/components/Link.vue'
import SolidBadge from '@/components/SolidBadge.vue'

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
    paymentMethods: Object,
  },

  setup () {

  }
})
</script>
