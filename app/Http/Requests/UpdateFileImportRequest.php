<?php

namespace App\Http\Requests;

class UpdateFileImportRequest extends CreateFileImportRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'files' => ['array', 'required'],
            'heading_row' => ['required', 'integer'],
            'starting_row' => ['required', 'integer'],
        ];
    }
}
