<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;

class CreateFileImportRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'files' => ['array', 'required'],
            'files.0.file' => ['file', 'mimes:txt,csv,xls,xlsx'],
            'heading_row' => ['required', 'integer', 'min:1'],
            'starting_row' => ['required', 'integer', 'min:1'],
        ];
    }

    public function getFile(): UploadedFile
    {
        $data = $this->validated();

        $fileData = Arr::first($data['files']);

        return $fileData['file'];
    }
}
