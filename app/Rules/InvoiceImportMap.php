<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class InvoiceImportMap implements Rule
{
    protected bool $required;
    protected string|array $rules;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(string|array $rules = '', bool $required = false)
    {
        $this->rules = $rules;
        $this->required = $required;
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
        $requiredKeys = ['id', 'isManual', 'column', 'value'];

        if (
            (!empty(array_diff($requiredKeys, array_keys($value)))) ||
            ($this->required && !$value['isManual'] && !$value['column'])
        ) {
            return false;
        }

        if (!$value['isManual'] && $value['column']) {
            return true;
        }

        $validator = Validator::make($value, [
            'value' => $this->rules,
        ]);

        return $validator->passes();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('You must map this field to a column or provide a custom value.');
    }
}
