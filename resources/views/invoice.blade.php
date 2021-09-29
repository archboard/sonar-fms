@php
/** @var \App\Models\Invoice $invoice */
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0" />
    <title>{{ $title }}</title>
    <link href="{{ url('/css/ckeditor.css') }}" rel="stylesheet"/>
    <link href="{{ url('/css/pdf.css') }}" rel="stylesheet"/>
    <style>
      html {
        font-size: 12px;
      }
    </style>
  </head>
  <body class="bg-gray-50 text-gray-900 text-base">
    <div class="bg-white min-h-screen mx-auto p-8 space-y-8" style="max-width:{{ $layout->max_width }};">
      @foreach($layout->layout_data['rows'] as $row)
        @if($row['isInvoiceTable'])
          @if($invoice->children->isEmpty())
            <x-invoice-table :invoice="$invoice" :currency="$currency" />
          @else
            @foreach($invoice->children as $child)
              <x-invoice-table :invoice="$child" :currency="$currency" />
            @endforeach
          @endif
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

      @php
        /** @var \Illuminate\Support\Collection $paymentMethods */
        $paymentMethods = $invoice->school->paymentMethods()->showOnInvoice()->get();
      @endphp

      @if($paymentMethods->isNotEmpty())
        <div>
          <h2 class="font-bold text-lg pt-4 mb-4 border-t">{{ __('Available payment methods') }}</h2>
          <ul class="space-y-4">
            @foreach($paymentMethods as $method)
              <li>
                <p>
                  <strong>{{ $method->getDriver()->label() }}</strong> {{ $method->invoice_description ? '- ' . $method->invoice_description : '' }}
                </p>
                @if($content = $method->getDriver()->getInvoiceContent())
                  <div class="mt-2">
                    {!! $content !!}
                  </div>
                @endif
              </li>
            @endforeach
          </ul>
        </div>
      @endif
    </div>
  </body>
</html>
