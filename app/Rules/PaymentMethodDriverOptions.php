<?php

namespace App\Rules;

use App\Models\PaymentMethod;
use App\PaymentMethods\PaymentMethodDriver;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class PaymentMethodDriverOptions implements Rule
{
    protected PaymentMethodDriver $driver;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(string $driverName)
    {
        $this->driver = PaymentMethod::makeDriver($driverName);
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
        $validator = Validator::make(
            $value,
            $this->driver->getValidationRules()
        );

        return $validator->passes();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('The payment method configuration is invalid.');
    }
}
