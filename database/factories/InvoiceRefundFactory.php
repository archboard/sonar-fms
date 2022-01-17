<?php

namespace Database\Factories;

use App\Models\School;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceRefundFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'tenant_id' => Tenant::current()?->id,
            'school_id' => School::current()?->id,
            'user_uuid' => auth()->user()?->uuid,
            'notes' => $this->faker->paragraph(),
            'transaction_details' => $this->faker->sentence(),
        ];
    }
}
