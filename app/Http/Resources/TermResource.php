<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TermResource extends JsonResource
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
            'abbreviation' => $this->abbreviation,
            'portion' => $this->portion,
            'start_year' => $this->start_year,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'is_current' => $this->is_current,
        ];
    }
}
