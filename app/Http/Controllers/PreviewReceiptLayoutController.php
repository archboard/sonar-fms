<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use Illuminate\Http\Request;

class PreviewReceiptLayoutController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function __invoke(Request $request, Receipt $receipt)
    {
        $title = $receipt->receipt_number;

        return view('receipt', [
            'title' => $title,
            'receipt' => $receipt,
        ]);
    }
}
