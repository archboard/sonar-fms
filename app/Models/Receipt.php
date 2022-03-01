<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use App\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin IdeHelperReceipt
 */
class Receipt extends Model
{
    use HasFactory;
    use BelongsToUser;
    use BelongsToSchool;

    protected $guarded = [];

    public function invoicePayment(): BelongsTo
    {
        return $this->belongsTo(InvoicePayment::class);
    }

    public function invoice(): HasOneThrough
    {
        return $this->hasOneThrough(
            Invoice::class,
            InvoicePayment::class,
            'uuid',
            'uuid',
            'invoice_payment_uuid',
            'invoice_uuid'
        );
    }

    public static function getDisk(): \Illuminate\Contracts\Filesystem\Filesystem|\Illuminate\Filesystem\FilesystemAdapter
    {
        return Storage::disk(config('filesystems.receipts'));
    }

    public function download(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $disk = static::getDisk();

        // If the pdf doesn't exist, try creating it
        if (!$disk->exists($this->path)) {
            $this->invoicePayment->saveReceiptPdf(receipt: $this);
        }

        return static::getDisk()->download($this->path);
    }
}
