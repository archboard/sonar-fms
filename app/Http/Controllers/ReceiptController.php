<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Receipt::class, 'receipt');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Receipt $receipt
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function show(Receipt $receipt)
    {
        return $receipt->download();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Receipt  $receipt
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Receipt $receipt)
    {
        $receipt->update(['voided_at' => now()]);

        session()->flash('success', __('Receipt voided successfully.'));

        return back();
    }
}
