<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class InvoiceImportAmountOrPercentage implements Rule
{
    public array $data;
    public string $key;
    public ?string $message;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(array $data, string $key, string $message = null)
    {
        $this->data = $data;
        $this->key = $key;
        $this->message = $message;
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
//        ray($attribute, $value);
        $prefix = Str::beforeLast($attribute, '.');
        $useAmount = Arr::get($this->data, "{$prefix}.use_amount");

        // If it is set to use the amount, validate the amount
        if (
            ($useAmount && Str::endsWith($attribute, 'amount')) ||
            (!$useAmount && Str::endsWith($attribute, 'percentage'))
        ) {
            return Validator::make(['value' => $value], [
                'value' => new InvoiceImportMap('required', true)
            ])->passes();
        }

        // Get the percentage value
//        $without = Arr::get($this->data, "{$prefix}.{$this->key}");
//        ray('percentage', $without);
//
//        return Validator::make(['percentage' => $without], [
//            'percentage' => new InvoiceImportMap('required', true)
//        ])->passes();

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message ?? __('This field is required.');
    }
}
