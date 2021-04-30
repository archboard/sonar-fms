<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SchoolResource extends JsonResource
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
            'sis_id' => $this->sis_id,
            'active' => $this->active,
            'low_grade' => $this->low_grade,
            'high_grade' => $this->high_grade,
            'currency_id' => $this->currency_id,
            'grade_levels' => $this->grade_levels,
            'school_number' => $this->school_number,
            'currency' => new CurrencyResource($this->whenLoaded('currency')),
        ];
    }
}
