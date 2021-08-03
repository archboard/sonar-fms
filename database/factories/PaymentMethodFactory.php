<?php

namespace Database\Factories;

use App\Models\PaymentMethod;
use App\Models\School;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentMethodFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PaymentMethod::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'tenant_id' => Tenant::current()->id,
            'school_id' => School::current()->id,
            'driver' => $this->faker->randomElement(['cash']),
            'invoice_description' => $this->faker->sentence,
            'show_on_invoice' => true,
            'active' => true,
            'options' => [],
        ];
    }
}
