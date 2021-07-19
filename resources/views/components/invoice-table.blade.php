@props(['invoices' => []])

<div class="space-y-8">
  @foreach($invoices as $invoice)
    <div>
      <h2 class="text-lg font-bold">{{ $invoice->student->full_name }} <span class="text-gray-500">({{ $invoice->student->student_number }})</span></h2>
      <div class="text-gray-700 text-sm">{{ $invoice->student->grade_level_formatted }}</div>
      <table class="w-full mt-4">
        <thead>
          <tr>
            <th class="text-left px-6 py-3 text-sm font-medium bg-gray-200 text-gray-900 border">{{ __('Item') }}</th>
            <th class="text-right px-6 py-3 text-sm font-medium bg-gray-200 text-gray-900 border">{{ __('Price') }}</th>
            <th class="text-left px-6 py-3 text-sm font-medium bg-gray-200 text-gray-900 border">{{ __('Quantity') }}</th>
            <th class="text-right px-6 py-3 text-sm font-medium bg-gray-200 text-gray-900 border">{{ __('Total') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach($invoice->invoiceItems as $invoiceItem)
            <tr>
              <td class="text-left px-6 py-3 text-sm text-gray-900 border-b">{{ $invoiceItem->name }}</td>
              <td class="text-right px-6 py-3 text-sm text-gray-900 border-b">{{ $invoiceItem->amount_per_unit_formatted }}</td>
              <td class="text-left px-6 py-3 text-sm text-gray-900 border-b">{{ $invoiceItem->quantity }}</td>
              <td class="text-right px-6 py-3 text-sm text-gray-900 border-b">{{ $invoiceItem->amount_formatted }}</td>
            </tr>
          @endforeach
          <tr>
            <td class="font-bold text-left px-6 py-3 text-sm" colspan="3">
              {{ __('Subtotal') }}
            </td>
            <td class="font-bold text-right px-6 py-3 text-sm">
              {{ $invoice->amount_formatted }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  @endforeach
</div>
