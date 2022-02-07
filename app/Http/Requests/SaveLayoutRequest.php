<?php

namespace App\Http\Requests;

use App\Rules\PdfLayoutData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveLayoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|max:255',
            'locale' => 'nullable',
            'paper_size' => Rule::in(['A4', 'Letter']),
            'layout_data' => ['required', new PdfLayoutData()],
            'layout_data.rows' => ['required', 'array'],
        ];
    }
}
