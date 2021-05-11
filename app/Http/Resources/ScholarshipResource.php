<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ScholarshipResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'amount' => $this->amount,
            'percentage' => $this->percentage,
            'percentage_formatted' => $this->percentage_formatted,
            'resolution_strategy' => $this->resolution_strategy,
            'created_at' => $this->created_at,
        ];
    }
}
