<?php

namespace App\Models;

use App\Http\Requests\CreateInvoiceRequest;
use App\Jobs\SendNewInvoiceNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Ramsey\Uuid\Uuid;

/**
 * @mixin IdeHelperInvoice
 */
class Invoice extends Model
{
    protected $guarded = [];

    protected $casts = [
        'notify_now' => 'boolean',
        'due_at' => 'datetime',
        'voided_at' => 'datetime',
        'paid_at' => 'datetime',
        'notify_at' => 'datetime',
        'notified_at' => 'datetime',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_uuid', 'uuid');
    }

    public static function getAttributesFromRequest(
        CreateInvoiceRequest $request,
        Student $student
    ): array
    {
        $data = $request->validated();
        $invoiceAttributes = Arr::except($data, 'items');

        $invoiceAttributes['uuid'] = Uuid::uuid4();
        $invoiceAttributes['school_id'] = $request->school()->id;
        $invoiceAttributes['student_id'] = $student->id;

        return $invoiceAttributes;
    }

    public function queueNotification(Carbon $notifyAt): static
    {
        $notifyAt->startOfMinute();

        $this->update([
            'notify' => true,
            'notify_at' => $notifyAt,
            'notified_at' => null,
        ]);

        // Dispatch the notification for 15 minutes
        SendNewInvoiceNotification::dispatch($this)
            ->delay($notifyAt);

        return $this;
    }

    public function notifyLater(Carbon $dateTime = null): static
    {
        return $this->queueNotification($dateTime ?? now()->addMinutes(15));
    }

    public function notifyNow(): static
    {
        return $this->queueNotification(now());
    }

    public function cancelNotification(): static
    {
        $this->update([
            'notify' => false,
            'notify_at' => null,
            'notified_at' => null,
        ]);

        return $this;
    }
}
