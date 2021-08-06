<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class CreateInvoiceForStudentsRequest extends CreateInvoiceRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules['students'] = [
            'required',
            'array',
            Rule::in($this->school()->students->pluck('id')),
        ];

        return $rules;
    }
}
