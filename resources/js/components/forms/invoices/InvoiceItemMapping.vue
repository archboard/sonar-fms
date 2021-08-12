<template>
  <div class="mb-6">
    <CardSectionHeader>
      {{ __('Invoice line items') }}
    </CardSectionHeader>
    <HelpText class="text-sm mt-1">
      {{ __('Add line items to the build the invoice and total receivable amount.') }}
    </HelpText>
  </div>

  <Error v-if="form.errors.items" class="mb-4">
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
                <MapField v-model="item.fee_id" :headers="headers" :id="`fee_id_${index}`">
                  <Select
                    v-model="item.fee_id.value" :id="`fee_id_${index}`"
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
                  <template v-slot:after>
                    <HelpText>
                      {{ __("Associating line items with a fee will help with reporting and syncing data, but isn't required.") }}
                    </HelpText>
                  </template>
                </MapField>
              </InputWrap>

              <InputWrap :error="form.errors[`items.${index}.name`]">
                <Label :for="`name_${index}`" :required="true">{{ __('Name') }}</Label>
                <MapField v-model="item.name" :headers="headers" :id="`name_${index}`">
                  <Input v-model="item.name.value" :id="`name_${index}`" />
                  <template v-slot:after>
                    <HelpText>
                      {{ __('This is the label given to the line item and will be displayed on the invoice.') }}
                    </HelpText>
                  </template>
                </MapField>
              </InputWrap>

              <InputWrap :error="form.errors[`items.${index}.amount_per_unit`]">
                <Label :for="`amount_per_unit_${index}`" :required="true">{{ __('Amount per unit') }}</Label>
                <MapField v-model="item.amount_per_unit" :headers="headers" :id="`amount_per_unit_${index}`">
                  <CurrencyInput v-model="item.amount_per_unit.value" :id="`amount_per_unit_${index}`" />
                </MapField>
              </InputWrap>

              <InputWrap :error="form.errors[`items.${index}.quantity`]">
                <Label :for="`quantity_${index}`" :required="true">{{ __('Quantity') }}</Label>
                <MapField v-model="item.quantity" :headers="headers" :id="`quantity_${index}`">
                  <Input v-model="item.quantity.value" :id="`quantity_${index}`" type="number" />
                </MapField>
              </InputWrap>

              <div class="flex justify-end">
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

  <AddThingButton @click="addInvoiceLineItem">
    {{ __('Add invoice line item') }}
  </AddThingButton>
</template>

<script>
import { defineComponent } from 'vue'
import Error from '@/components/forms/Error'
import InvoiceMappingFormCollection from '@/mixins/InvoiceMappingFormCollection'
import hasModelValue from '@/composition/hasModelValue'
import invoiceImportItemForm from '@/composition/invoiceImportItemForm'

export default defineComponent({
  mixins: [InvoiceMappingFormCollection],
  components: {
    Error,
  },

  setup (props, context) {
    const { localValue } = hasModelValue(props, context.emit)
    const {
      fees,
      makeInvoiceLineItem,
      feeSelected
    } = invoiceImportItemForm()
    const addInvoiceLineItem = () => {
      localValue.value.push(makeInvoiceLineItem())
    }

    // Add an initial line item
    if (localValue.value.length === 0) {
      addInvoiceLineItem()
    }

    return {
      localValue,
      fees,
      addInvoiceLineItem,
      feeSelected,
    }
  }
})
</script>
