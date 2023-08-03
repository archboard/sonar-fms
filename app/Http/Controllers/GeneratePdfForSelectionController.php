<?php

namespace App\Http\Controllers;

use App\Jobs\CreateInvoicePdf;
use App\Models\User;
use App\Notifications\InvoicePdfBatchFailed;
use App\Notifications\InvoicePdfBatchFinished;
use Illuminate\Bus\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class GeneratePdfForSelectionController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $batch = Bus::batch(
                $user->selectedInvoices()
                    ->pluck('uuid')
                    ->map(fn ($uuid) => new CreateInvoicePdf(invoiceUuid: $uuid, force: true))
            )
            ->then(function (Batch $batch) use ($user) {
                $user->notify(new InvoicePdfBatchFinished());
            })
            ->catch(function (Batch $batch, \Throwable $e) use ($user) {
                Log::error($e->getMessage());
                $user->notify(new InvoicePdfBatchFailed());
            })
            ->dispatch();

        $user->invoiceSelections()->delete();

        session()->flash('success', __('Started creating PDFs. You will receive after it is done.'));

        return back();
    }
}
