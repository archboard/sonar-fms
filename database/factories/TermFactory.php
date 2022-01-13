<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\Term;
use Illuminate\Database\Eloquent\Factories\Factory;

class TermFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Term::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'tenant_id' => Tenant::current()?->id,
            'sis_id' => $this->faker->numberBetween(),
            'sis_assigned_id' => $this->faker->numberBetween(),
            'name' => today()->format('Y') . '-' . today()->addYear()->format('Y'),
            'abbreviation' => today()->format('y') . '-' . today()->addYear()->format('y'),
            'start_year' => today()->format('Y'),
            'portion' => 1,
            'starts_at' => today()->subMonth(),
            'ends_at' => today()->addMonths(6),
        ];
    }

    public function past(): static
    {
        return $this->state([
            'starts_at' => now()->subMonths(8),
            'ends_at' => now()->subMonth(),
        ]);
    }

    public function semester(): static
    {
        return $this->state([
            'name' => 'Semester',
            'abbreviation' => 'SX',
            'portion' => 2,
        ]);
    }
}
