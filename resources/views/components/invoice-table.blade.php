@php
/** @var \App\Models\Invoice $invoice */
@endphp

@props(['invoices' => [], 'currency'])

<div class="space-y-8">
  @foreach($invoices as $invoice)
    <div>
      <div class="flex justify-between">
        <div class="">
          <div class="text-lg font-medium">{{ $invoice->title }}</div>
          <div class="text-gray-700 text-sm">{{ __('Invoice #:number', ['number' => $invoice->id]) }}</div>
        </div>
        <div class="text-right">
          <h3 class="text-lg font-medium">{{ $invoice->student->full_name }} <span class="text-gray-500">({{ $invoice->student->student_number }})</span></h3>
          <div class="text-gray-700 text-sm">{{ $invoice->student->grade_level_formatted }}</div>
        </div>
      </div>

      @if($invoice->description)
        <div class="mt-4 text-sm">{{ $invoice->description }}</div>
      @endif

      <table class="w-full mt-4">
        <thead>
          <tr>
            <th class="text-left px-4 py-2 text-sm font-medium bg-gray-200 text-gray-900 border">{{ __('Item') }}</th>
            <th class="text-right px-4 py-2 text-sm font-medium bg-gray-200 text-gray-900 border">{{ __('Price') }}</th>
            <th class="text-left px-4 py-2 text-sm font-medium bg-gray-200 text-gray-900 border">{{ __('Quantity') }}</th>
            <th class="text-right px-4 py-2 text-sm font-medium bg-gray-200 text-gray-900 border">{{ __('Total') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach($invoice->invoiceItems as $invoiceItem)
            <tr>
              <td class="text-left px-4 py-2 text-sm text-gray-900 border-b">{{ $invoiceItem->name }}</td>
              <td class="text-right px-4 py-2 text-sm text-gray-900 border-b">{{ displayCurrency($invoiceItem->amount_per_unit, $currency) }}</td>
              <td class="text-left px-4 py-2 text-sm text-gray-900 border-b">{{ $invoiceItem->quantity }}</td>
              <td class="text-right px-4 py-2 text-sm text-gray-900 border-b">{{ displayCurrency($invoiceItem->amount, $currency) }}</td>
            </tr>
          @endforeach
          <tr>
            <td class="font-bold text-left px-4 py-2 text-sm" colspan="3">
              {{ __('Subtotal') }}
            </td>
            <td class="font-bold text-right px-4 py-2 text-sm">
              {{ displayCurrency($invoice->subtotal, $currency) }}
            </td>
          </tr>
        </tbody>
      </table>

      @if($invoice->invoiceScholarships->isNotEmpty())
        <table class="w-full mt-4">
          <thead>
            <tr>
              <th class="text-left px-4 py-2 text-sm font-medium bg-gray-200 text-gray-900 border">{{ __('Scholarship') }}</th>
              <th class="text-right px-4 py-2 text-sm font-medium bg-gray-200 text-gray-900 border">{{ __('Amount') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach($invoice->invoiceScholarships as $invoiceScholarship)
              <tr>
                <td class="text-left px-4 py-2 text-sm text-gray-900 border-b">
                  {{ $invoiceScholarship->name }}
                  @if($invoiceScholarship->percentage)
                    <span class="text-gray-500">({{ $invoiceScholarship->percentage_formatted }})</span>
                  @endif
                </td>
                <td class="text-right px-4 py-2 text-sm text-gray-900 border-b">{{ displayCurrency($invoiceScholarship->calculated_amount, $currency) }}</td>
              </tr>
            @endforeach
            <tr>
              <td class="font-bold text-left px-4 py-2 text-sm">
                {{ __('Scholarship total') }}
              </td>
              <td class="font-bold text-right px-4 py-2 text-sm">
                {{ displayCurrency($invoice->discount_total, $currency) }}
              </td>
            </tr>
          </tbody>
        </table>
      @endif

      <table class="w-full mt-4 border-2 border-gray-500">
        <tbody>
          <tr>
            <td class="font-bold text-left px-4 py-2">
              @if($invoice->due_at)
                {{ __('Amount due by :date', ['date' => $invoice->due_at->setTimezone($invoice->school->timezone)->format('F j, Y H:i')]) }}
              @else
                {{ __('Amount due') }}
              @endif
            </td>
            <td class="font-bold text-right px-4 py-2">
              {{ displayCurrency($invoice->amount_due, $currency) }}
            </td>
          </tr>
        </tbody>
      </table>

      @if($invoice->invoicePaymentSchedules->isNotEmpty())
        <div class="my-6 relative">
          <div class="absolute inset-0 flex items-center" aria-hidden="true">
            <div class="w-full border-t border-gray-300"></div>
          </div>
          <div class="relative flex justify-center text-sm">
            <span class="px-2 bg-white text-gray-500">
              {{ __('Or use a payment schedule') }}
            </span>
          </div>
        </div>

        <div class="mt-6">
{{--          <h2 class="font-bold text-lg mb-4">{{ __('Available payment schedules') }}</h2>--}}
          <div class="space-y-4">
            @foreach($invoice->invoicePaymentSchedules as $paymentSchedule)
              <div>
                <h3 class="font-medium mb-2">{{ __(':number payments (:total_price)', ['number' => $paymentSchedule->invoicePaymentTerms->count(), 'total_price' => displayCurrency($paymentSchedule->amount, $currency)]) }}</h3>
{{--                <div class="grid grid-cols-4 gap-4">--}}
                <div class="flex items-start space-x-4">
                  @foreach($paymentSchedule->invoicePaymentTerms as $term)
                    <div class="flex-0 w-full border p-4">
                      @if($term->due_at)
                        {{ __(':amount due by :date', ['amount' => displayCurrency($term->amount_due, $currency), 'date' => $term->due_at->setTimezone($invoice->school->timezone)->format('F j, Y H:i')]) }}
                      @else
                        {{ displayCurrency($term->amount_due, $currency) }}
                      @endif
                    </div>
                  @endforeach
                </div>
              </div>
            @endforeach
          </div>
        </div>
      @endif
    </div>
  @endforeach
</div>
