<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0" />
    <title>{{ $title }}</title>
    <link href="{{ mix('/css/ckeditor.css') }}" rel="stylesheet" />
    <link href="{{ mix('/css/app.css') }}" rel="stylesheet" />
    <style>
      html {
        font-size: 12px;
      }
    </style>
  </head>
  <body class="bg-gray-50 text-gray-900">
    <div class="bg-white min-h-screen mx-auto p-8 space-y-8" style="max-width:{{ $layout->max_width }};">
      @foreach($layout->layout_data['rows'] as $row)
        @if($row['isInvoiceTable'])
          <x-invoice-table :invoices="$invoices" :currency="$currency" />
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
