<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceImportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'mapping' => $this->mapping,
            'total_records' => $this->total_records,
            'imported_records' => $this->imported_records,
            'failed_records' => $this->failed_records,
            'imported_at' => $this->imported_at,
            'heading_row' => $this->heading_row,
            'files' => [
                [
                    'id' => $this->id,
                    'name' => $this->file_name,
                    'file' => $this->file_path,
                    'existing' => true,
                ],
            ]
        ];
    }
}
