<?php

namespace Database\Factories;

use App\Models\InvoicePaymentTerm;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InvoicePaymentTermFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = InvoicePaymentTerm::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $amount = $this->faker->numberBetween(1000);

        return [
            'uuid' => Str::uuid()->toString(),
            'amount' => $amount,
            'amount_due' => $amount,
            'remaining_balance' => $amount,
        ];
    }
}
