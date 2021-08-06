<template>
  <div class="mb-6">
    <CardSectionHeader>
      {{ __('Invoice line items') }}
    </CardSectionHeader>
    <HelpText class="text-sm mt-1">
      {{ __('Add line items to the build the invoice and total receivable amount.') }}
    </HelpText>
  </div>

  <Error v-if="form.errors.items">
    {{ __('You must have at least one invoice item.') }}
  </Error>

  <ul class="space-y-3">
    <FadeInGroup>
      <li
        v-for="(item, index) in localValue"
        :key="item.id"
      >
        <CardWrapper>
          <CardPadding>
            <Fieldset>
              <InputWrap :error="form.errors[`items.${index}.fee_id`]">
                <Label :for="`fee_id_${index}`">{{ __('Fee') }}</Label>
                <Select
                  v-model="item.fee_id" :id="`fee_id_${index}`"
                  @change="feeSelected(item)"
                >
                  <option :value="null">{{ __('Use a custom fee') }}</option>
                  <option
                    v-for="fee in fees"
                    :key="fee.id"
                    :value="fee.id"
                  >
                    {{ fee.name }}{{ fee.code ? ` (${fee.code})` : '' }} - {{ fee.amount_formatted }}
                  </option>
                </Select>
                <HelpText>
                  {{ __("Associating line items with a fee will help with reporting and syncing data, but isn't required.") }}
                </HelpText>
              </InputWrap>

              <InputWrap :error="form.errors[`items.${index}.name`]">
                <Label :for="`name_${index}`" :required="true">{{ __('Name') }}</Label>
                <Input v-model="item.name" :id="`name_${index}`" />
                <HelpText>
                  {{ __('This is the label given to the line item and will be displayed on the invoice.') }}
                </HelpText>
              </InputWrap>

              <InputWrap :error="form.errors[`items.${index}.amount_per_unit`]">
                <Label :for="`amount_per_unit_${index}`" :required="true">{{ __('Amount per unit') }}</Label>
                <CurrencyInput v-model="item.amount_per_unit" :id="`amount_per_unit_${index}`" />
              </InputWrap>

              <InputWrap :error="form.errors[`items.${index}.quantity`]">
                <Label :for="`quantity_${index}`" :required="true">{{ __('Quantity') }}</Label>
                <Input v-model="item.quantity" :id="`quantity_${index}`" type="number" />
              </InputWrap>

              <div class="flex justify-between items-center">
                <h4 class="font-bold">
                  {{ __('Line item total: :total', { total: displayCurrency(item.amount_per_unit * item.quantity) }) }}
                </h4>
                <Button color="red" size="sm" type="button" @click.prevent="localValue.splice(index, 1)">
                  <TrashIcon class="w-4 h-4" />
                  <span class="ml-2">{{ __('Remove line item') }}</span>
                </Button>
              </div>
            </Fieldset>
          </CardPadding>
        </CardWrapper>
      </li>
    </FadeInGroup>
  </ul>

  <FadeIn>
    <CardWrapper v-if="localValue.length > 0" class="my-4">
      <CardPadding>
        <div class="flex justify-between">
          <h4 class="font-bold">
            {{ __('Invoice subtotal') }}
          </h4>
          <div class="font-bold">
            {{ displayCurrency(subtotal) }}
          </div>
        </div>
      </CardPadding>
    </CardWrapper>
  </FadeIn>

  <div class="relative flex justify-center mt-6">
    <button @click.prevent="addInvoiceLineItem" type="button" class="inline-flex items-center shadow-sm px-4 py-1.5 border border-gray-300 dark:border-gray-600 text-sm leading-5 font-medium rounded-full text-gray-700 dark:text-gray-100 bg-white dark:bg-gray-500 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
      <PlusSmIcon class="-ml-1.5 mr-1 h-5 w-5 text-gray-400 dark:text-gray-200" aria-hidden="true" />
      <span>{{ __('Add invoice line item') }}</span>
    </button>
  </div>
</template>

<script>
import { defineComponent } from 'vue'
import invoiceItemForm from '@/composition/invoiceItemForm'
import invoiceFormComponent from '@/composition/invoiceFormComponent'
import InvoiceFormCollection from '@/mixins/InvoiceFormCollection'

export default defineComponent({
  mixins: [InvoiceFormCollection],

  setup (props, context) {
    const { localValue, displayCurrency } = invoiceFormComponent(props, context)
    const {
      fees,
      subtotal,
      addInvoiceLineItem,
      feeSelected
    } = invoiceItemForm(props.form)

    return {
      localValue,
      displayCurrency,
      fees,
      subtotal,
      addInvoiceLineItem,
      feeSelected,
    }
  }
})
</script>
