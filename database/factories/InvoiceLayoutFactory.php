<?php

namespace Database\Factories;

use App\Models\InvoiceLayout;
use App\Models\School;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceLayoutFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = InvoiceLayout::class;

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
            'name' => $this->faker->words(3, true),
            'paper_size' => $this->faker->randomElement(['A4', 'Letter']),
            'layout_data' => [
                'rows' => [],
                'primary' => '#fff',
                'logo' => '',
            ],
        ];
    }
}
