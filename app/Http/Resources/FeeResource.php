<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FeeResource extends JsonResource
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
            'code' => $this->code,
            'description' => $this->description,
            'amount' => $this->amount,
            'fee_category' => new FeeCategoryResource($this->whenLoaded('feeCategory')),
            'department' => new DepartmentResource($this->whenLoaded('department')),
        ];
    }
}
