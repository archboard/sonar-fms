<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperInvoiceLayout
 */
class InvoiceLayout extends LayoutBase
{
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
