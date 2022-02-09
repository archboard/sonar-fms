<?php

namespace App\Models;

use App\Jobs\MakeReceipt;
use App\Traits\BelongsToInvoice;
use App\Traits\BelongsToSchool;
use App\Traits\BelongsToTenant;
use App\Traits\HasAmountAttribute;
use App\Traits\ScopeToCurrentSchool;
use App\Traits\UsesUuid;
use GrantHolle\Http\Resources\Traits\HasResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use JamesMills\LaravelTimezone\Facades\Timezone;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Browsershot\Browsershot;

/**
 * @mixin IdeHelperInvoicePayment
 */
class InvoicePayment extends Model
{
    use HasFactory;
    use HasResource;
    use BelongsToTenant;
    use BelongsToSchool;
    use BelongsToInvoice;
    use HasAmountAttribute;
    use UsesUuid;
    use LogsActivity;

    protected $guarded = [];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => 'int',
    ];

    protected static array $recordEvents = ['updated'];

    protected static function booted()
    {
        static::saved(function (InvoicePayment $payment) {
            MakeReceipt::dispatch($payment);
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty();
    }

    public function scopeFilter(Builder $builder, array $filters)
    {
        $builder->select('invoice_payments.*');

        $builder->when($filters['s'] ?? null, function (Builder $builder, string $search) {
            $builder->whereHas('invoice', function (Builder $builder) use ($search) {
                $builder->search($search); // @phpstan-ignore-line
            });
        })->when($filters['start_amount'] ?? null, function (Builder $builder, $amount) {
            $builder->where('invoice_payments.amount', '>=', $amount);
        })->when($filters['end_amount'] ?? null, function (Builder $builder, $amount) {
            $builder->where('invoice_payments.amount', '<=', $amount);
        })->when($filters['start_date'] ?? null, function (Builder $builder, $date) {
            $builder->where('invoice_payments.paid_at', '>=', $date);
        })->when($filters['end_date'] ?? null, function (Builder $builder, $date) {
            $builder->where('invoice_payments.paid_at', '<=', $date);
        })->whereNull('invoice_payments.parent_uuid');

        $builder->join('invoices', 'invoice_payments.invoice_uuid', '=', 'invoices.uuid');
        $orderDir = $filters['orderDir'] ?? 'desc';
        $orderBy = $filters['orderBy'] ?? 'paid_at';

        $builder->orderBy($orderBy, $orderDir)
            ->orderBy('invoice_payments.created_at', $orderDir);
    }

    public function getPaidAtFormattedAttribute(): string
    {
        if (!$this->paid_at) {
            return '';
        }

        return Timezone::convertToLocal($this->paid_at, 'M j, Y');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(static::class, 'parent_uuid', 'uuid');
    }

    public function invoicePaymentTerm(): BelongsTo
    {
        return $this->belongsTo(
            InvoicePaymentTerm::class,
            'invoice_payment_term_uuid',
            'uuid'
        );
    }

    public function invoicePaymentSchedule(): HasOneThrough
    {
        return $this->hasOneThrough(
            InvoicePaymentSchedule::class,
            InvoicePaymentTerm::class,
            'uuid',
            'uuid',
            'invoice_payment_term_uuid',
            'invoice_payment_schedule_uuid'
        );
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by', 'uuid');
    }

    public function madeBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'made_by', 'uuid');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class);
    }

    public static function getLoadAttributes(): array
    {
        return [
            'currency',
            'invoice',
            'invoice.student',
            'invoice.students',
            'invoice.currency',
            'parent',
            'invoicePaymentTerm',
            'recordedBy',
            'madeBy',
            'invoicePaymentSchedule.invoicePaymentTerms',
            'invoicePaymentTerm',
            'paymentMethod',
        ];
    }

    public function fullLoad(): static
    {
        return $this->load(static::getLoadAttributes());
    }

    public function makeReceipt(User $user): Receipt
    {
        $count = Str::padLeft($this->receipts()->count(), 2, '0');

        return new Receipt([
            'tenant_id' => $this->tenant_id,
            'school_id' => $this->school_id,
            'user_id' => $user->uuid,
            'invoice_payment_uuid' => $this->uuid,
            'receipt_number' => "{$this->invoice->invoice_number}-R{$count}",
        ]);
    }

    public static function getReceiptDisk(): \Illuminate\Contracts\Filesystem\Filesystem|\Illuminate\Filesystem\FilesystemAdapter
    {
        return Storage::disk(config('filesystems.receipts'));
    }

    public function generatePdfPath(Receipt $receipt): string
    {
        $parameters = [
            $this->tenant_id,
            $this->school_id,
            $this->created_at->format('Y'),
            $this->created_at->format('n'),
            $this->invoice->invoice_number,
            $receipt->receipt_number,
        ];

        return Str::replaceArray('?', $parameters, '?/?/?/?/?/?.pdf');
    }

    public function saveReceiptPdf(?ReceiptLayout $layout = null): Receipt
    {
        $receipt = $this->makeReceipt($this->recordedBy);
        $layout = $layout ?? $this->school->getDefaultReceiptLayout();
        $title = __('Receipt :number', ['number' => $receipt->receipt_number]);

        $content = view('receipt', [
            'title' => $title,
            'currency' => $this->currency,
        ])->render();

        $userDir = realpath(sys_get_temp_dir() . "/sonar-fms-pdf/receipts-{$layout->id}");
        $disk = static::getReceiptDisk();
        $receipt->path = $this->generatePdfPath($receipt);

        $disk->makeDirectory(dirname($receipt->path));

        Browsershot::html($content)
            ->disableJavascript()
            ->margins(0, 0, 0, 0)
            ->format($layout->paper_size)
            ->noSandbox()
            ->showBackground()
            ->setNodeBinary(config('services.node.binary'))
            ->setNpmBinary(config('services.node.npm'))
            ->addChromiumArguments([
                'user-data-dir' => $userDir
            ])
            ->ignoreHttpsErrors()
            ->hideHeader()
            ->hideFooter()
            ->savePdf($disk->path($receipt->path));

        $receipt->save();

        return $receipt;
    }
}
