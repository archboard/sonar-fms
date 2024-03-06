<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RecordExport>
 */
class RecordExportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'school_id' => School::current()?->id,
            'name' => Str::random(8),
            'format' => $this->faker->randomElement(['xlsx', 'csv']),
            'apply_filters' => $this->faker->boolean(),
            'model' => $this->faker->randomElement([
                Invoice::class,
            ]),
            'filters' => [
                's' => '',
                'status' => [],
                'date_start' => null,
                'date_end' => null,
            ],
        ];
    }
}
