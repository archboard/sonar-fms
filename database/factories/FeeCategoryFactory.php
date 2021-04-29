<?php

namespace Database\Factories;

use App\Models\FeeCategory;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeeCategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FeeCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'tenant_id' => Tenant::current()->id,
            'name' => $this->faker->word,
        ];
    }
}
