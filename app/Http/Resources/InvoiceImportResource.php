<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceImportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'mapping' => $this->mapping,
            'total_records' => $this->total_records,
            'imported_records' => $this->imported_records,
            'failed_records' => $this->failed_records,
            'imported_at' => $this->imported_at,
            'rolled_back_at' => $this->rolled_back_at,
            'imported_at_formatted' => optional($this->imported_at)->format('F j, Y'),
            'heading_row' => $this->heading_row,
            'starting_row' => $this->starting_row,
            'mapping_valid' => $this->mapping_valid,
            'file_name' => $this->file_name,
            // This is purely for the edit form,
            // as this is the structure it expects
            'files' => [
                [
                    'id' => $this->id,
                    'name' => $this->file_name,
                    'file' => $this->file_path,
                    'existing' => true,
                ],
            ],
        ];
    }
}
