<template>
  <div class="mb-6">
    <CardSectionHeader>
      {{ __('Invoice line items') }}<req />
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
                <Button color="red" size="sm" type="button" @click.prevent="removeInvoiceLineItem(item)">
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

  <AddThingButton @click="addInvoiceLineItem">
    {{ __('Add invoice line item') }}
  </AddThingButton>
</template>

<script>
import { defineComponent } from 'vue'
import invoiceItemForm from '@/composition/invoiceItemForm.js'
import invoiceFormComponent from '@/composition/invoiceFormComponent.js'
import InvoiceFormCollection from '@/mixins/InvoiceFormCollection'
import AddThingButton from '@/components/forms/AddThingButton.vue'
import Req from '@/components/forms/Req.vue'

export default defineComponent({
  components: {
    Req,
    AddThingButton
  },
  mixins: [InvoiceFormCollection],

  setup (props, context) {
    const { localValue, displayCurrency } = invoiceFormComponent(props, context)
    const {
      fees,
      subtotal,
      addInvoiceLineItem,
      removeInvoiceLineItem,
      feeSelected
    } = invoiceItemForm(props.form)

    return {
      localValue,
      displayCurrency,
      fees,
      subtotal,
      addInvoiceLineItem,
      removeInvoiceLineItem,
      feeSelected,
    }
  }
})
</script>
