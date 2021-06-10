<?php

namespace Database\Factories;

use App\Models\InvoiceTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceTemplateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = InvoiceTemplate::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->words(3, true),
            'template' => [
                'title' => 'Test invoice 2021',
                'description' => $this->faker->sentence,
                'available_at' => now()->subWeek()->format('Y-m-d\TH:i:s.v\Z'),
                'due_at' => now()->addMonth()->format('Y-m-d\TH:i:s.v\Z'),
                'term_id' => null,
                'notify' => false,
                'items' => [
                    [
                        'id' => $this->faker->uuid,
                        'fee_id' => null,
                        'name' => 'Line item 1',
                        'amount_per_unit' => 100,
                        'quantity' => 1,
                        'random_key' => 'random value',
                    ],
                    [
                        'id' => $this->faker->uuid,
                        'fee_id' => null,
                        'name' => 'Line item 2',
                        'amount_per_unit' => 100,
                        'quantity' => 2,
                    ]
                ],
                'scholarships' => [],
                'payment_schedules' => [],
            ],
        ];
    }
}
