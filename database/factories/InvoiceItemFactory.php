<?php

namespace Database\Factories;

use App\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InvoiceItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = InvoiceItem::class;

    public function configure()
    {
        return $this->afterMaking(function (InvoiceItem $item) {
            $item->setAmount();
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'uuid' => Str::uuid()->toString(),
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'amount_per_unit' => $this->faker->numberBetween(500000),
            'quantity' => $this->faker->numberBetween(1, 4),
        ];
    }
}
