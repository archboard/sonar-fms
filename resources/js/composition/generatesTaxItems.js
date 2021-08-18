import { watchEffect } from 'vue'
import useSchool from '@/composition/useSchool'
import invoiceImportMapField from '@/composition/invoiceImportMapField'


export default (localValue, isMapField = false) => {
  const { school } = useSchool()
  const { addMapFieldValue } = invoiceImportMapField()

  watchEffect(() => {
    localValue.value.tax_items = localValue.value.items.map(item => {
      const existingTaxItem = localValue.value.tax_items.find(t => t.item_id === item.id)

      if (existingTaxItem) {
        return {
          item_id: item.id,
          name: item.name,
          tax_rate: existingTaxItem.tax_rate,
          selected: existingTaxItem.selected,
        }
      }

      const taxRate = localValue.value.use_school_tax_defaults
          ? school.value.tax_rate_converted
          : localValue.value.tax_rate

      return {
        item_id: item.id,
        name: item.name,
        tax_rate: isMapField
          ? addMapFieldValue(taxRate)
          : taxRate,
        selected: false,
      }
    })
  })
}
