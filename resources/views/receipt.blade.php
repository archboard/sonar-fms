@php
/** @var \App\Models\Receipt $receipt */
/** @var \App\Models\InvoicePayment $payment */
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0" />
    <title>{{ $title }}</title>
    <link href="{{ url('/css/ckeditor.css') }}" rel="stylesheet"/>
    @if (app()->isLocal())
    <link href="{{ mix('/css/pdf.css') }}" rel="stylesheet"/>
    @else
    <link href="{{ url('/css/pdf.css') }}" rel="stylesheet"/>
    @endif
    <style>
      html {
        font-size: 12px;
      }
    </style>
  </head>
  <body class="bg-gray-50 text-gray-900 text-base">
    <div class="bg-white min-h-screen mx-auto p-8 space-y-8" style="max-width:{{ $layout->max_width }};">
      @foreach($layout->layout_data['rows'] as $row)
        @if($row['isContentTable'])
          <div>
            <h3 class="text-lg leading-6 font-medium text-gray-900">
              @if($payment->receipt?->receipt_number)
                {{ __('Receipt :number', ['number' => $payment->receipt->receipt_number]) }}
              @else
                {{ $title }}
              @endif
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">{{ __('Paid on :date', ['date' => $payment->paid_at->format('F j, Y')]) }}</p>
          </div>
          <div class="mt-5 border-t border-gray-200">
            <dl class="divide-y divide-gray-200">
              <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                <dt class="text-sm font-medium text-gray-500">{{ __('Invoice number') }}</dt>
                <dd class="mt-1 flex text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                  <span class="flex-grow">{{ $payment->invoice->invoice_number }}</span>
                </dd>
              </div>
              <div class="py-4 sm:py-5">
                @if($invoice->children->isEmpty())
                  <x-invoice-table :invoice="$invoice" :currency="$currency" :show-schedule="false" :show-total="false" />
                @else
                  <x-invoice-table :invoice="$invoice" :currency="$currency" :show-schedule="false" :show-total="false">
                    <div class="bg-gray-50 p-6 rounded-2xl space-y-6 my-6">
                      @foreach($invoice->children as $child)
                        @if(!$child->is_void)
                          <x-invoice-table :invoice="$child" :currency="$currency" :show-schedule="false" :show-total="false" />
                        @endif
                      @endforeach
                    </div>
                  </x-invoice-table>
                @endif
              </div>
{{--              <div class="py-4 sm:grid sm:py-5 sm:grid-cols-3 sm:gap-4">--}}
{{--                <dt class="text-sm font-medium text-gray-500">--}}
{{--                  @if($payment->invoice->students->isNotEmpty())--}}
{{--                    {{ __('Students') }}--}}
{{--                  @else--}}
{{--                    {{ __('Student') }}--}}
{{--                  @endif--}}
{{--                </dt>--}}
{{--                <dd class="mt-1 flex text-sm text-gray-900 sm:mt-0 sm:col-span-2">--}}
{{--                  @if($payment->invoice->students->isNotEmpty())--}}
{{--                    <ul>--}}
{{--                      @foreach($payment->invoice->students as $student)--}}
{{--                        <li>{{ $student->full_name }} ({{ $student->student_number }})</li>--}}
{{--                      @endforeach--}}
{{--                    </ul>--}}
{{--                  @else--}}
{{--                    {{ $payment->invoice->student->full_name }} ({{ $payment->invoice->student->student_number }})--}}
{{--                  @endif--}}
{{--                </dd>--}}
{{--              </div>--}}
              <div class="py-4 sm:grid sm:py-5 sm:grid-cols-3 sm:gap-4">
                <dt class="text-sm font-medium text-gray-500">{{ __('Payment amount') }}</dt>
                <dd class="mt-1 flex text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                  <span class="flex-grow">{{ displayCurrency($payment->amount, $currency) }}</span>
                </dd>
              </div>
              <div class="py-4 sm:grid sm:py-5 sm:grid-cols-3 sm:gap-4">
                <dt class="text-sm font-medium text-gray-500">{{ __('Date paid') }}</dt>
                <dd class="mt-1 flex text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                  <span class="flex-grow">{{ $payment->paid_at_formatted }}</span>
                </dd>
              </div>
              @if($payment->paymentMethod)
                <div class="py-4 sm:grid sm:py-5 sm:grid-cols-3 sm:gap-4">
                  <dt class="text-sm font-medium text-gray-500">{{ __('Payment method') }}</dt>
                  <dd class="mt-1 flex text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    <span class="flex-grow">
                      {{ $payment->paymentMethod->name }}
                    </span>
                  </dd>
                </div>
              @endif
              @if($payment->transaction_details)
                <div class="py-4 sm:grid sm:py-5 sm:grid-cols-3 sm:gap-4">
                  <dt class="text-sm font-medium text-gray-500">{{ __('Transaction details') }}</dt>
                  <dd class="mt-1 flex text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    <span class="flex-grow">
                      {{ $payment->transaction_details }}
                    </span>
                  </dd>
                </div>
              @endif
              @if($payment->payment_term_count > 0)
                <div class="py-4 sm:grid sm:py-5 sm:grid-cols-3 sm:gap-4">
                  <dt class="text-sm font-medium text-gray-500">{{ __('Payment schedule') }}</dt>
                  <dd class="mt-1 flex text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    <span class="flex-grow">
                      {{ __('Paid toward payment :number of :total_payments payments', [ 'number' => $payment->payment_term_count, 'total_payments' => $payment->invoicePaymentSchedule->invoicePaymentTerms->count()]) }}
                    </span>
                  </dd>
                </div>
              @endif
              <div class="py-4 sm:grid sm:py-5 sm:grid-cols-3 sm:gap-4">
                <dt class="text-sm font-medium text-gray-500">{{ __('Recorded by') }}</dt>
                <dd class="mt-1 flex text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                  <span class="flex-grow">
                    {{ $payment->recordedBy->full_name }}
                  </span>
                </dd>
              </div>
              <div class="py-4 sm:grid sm:py-5 sm:grid-cols-3 sm:gap-4">
                <dt class="text-sm font-medium text-gray-500">{{ __('Recorded on') }}</dt>
                <dd class="mt-1 flex text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                  <span class="flex-grow">
                    {{ $payment->created_at->format('F j, Y') }}
                  </span>
                </dd>
              </div>
              @if($payment->made_by)
                <div class="py-4 sm:grid sm:py-5 sm:grid-cols-3 sm:gap-4">
                  <dt class="text-sm font-medium text-gray-500">{{ __('Paid by') }}</dt>
                  <dd class="mt-1 flex text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    <span class="flex-grow">
                      {{ $payment->madeBy->full_name }}
                    </span>
                  </dd>
                </div>
              @endif
              <div class="py-4 sm:grid sm:py-5 sm:grid-cols-3 sm:gap-4">
                <dt class="text-sm font-bold text-gray-900">{{ __('Remaining balance at time of payment') }}</dt>
                <dd class="mt-1 flex text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                  <strong class="flex-grow">
                    {{ $payment->remaining_balance_formatted }}
                  </strong>
                </dd>
              </div>
            </dl>
          </div>

        @else
          <div class="ck-content flex items-start space-x-6">
            @foreach($row['columns'] as $column)
              <div class="flex-0 w-full">
                {!! $column['content'] !!}
              </div>
            @endforeach
          </div>
        @endif
      @endforeach
    </div>
  </body>
</html>
