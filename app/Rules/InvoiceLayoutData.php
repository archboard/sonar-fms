<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class InvoiceLayoutData implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return collect($value['rows'])
            ->some(fn ($row) => $row['isInvoiceTable']);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('One row must be the invoice table.');
    }
}
