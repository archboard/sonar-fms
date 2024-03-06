<?php

namespace App\Http\Controllers;

use App\Models\Receipt;

class ReceiptController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Receipt::class, 'receipt');
    }

    /**
     * Display the specified resource.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function show(Receipt $receipt)
    {
        return $receipt->download();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Receipt $receipt)
    {
        $receipt->update(['voided_at' => now()]);

        session()->flash('success', __('Receipt voided successfully.'));

        return back();
    }
}
