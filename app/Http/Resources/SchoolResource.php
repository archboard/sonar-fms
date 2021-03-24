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
            'low_grade' => $this->low_grade,
            'high_grade' => $this->high_grade,
            'grade_levels' => $this->grade_levels,
            'school_number' => $this->school_number,
            'currency_symbol' => $this->currency_symbol,
            'currency_decimals' => $this->currency_decimals,
        ];
    }
}
