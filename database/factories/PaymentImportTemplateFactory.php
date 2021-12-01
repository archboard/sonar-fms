<?php

namespace Database\Factories;

use App\Models\PaymentImportTemplate;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentImportTemplateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PaymentImportTemplate::class;

    protected function mapField(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'isManual' => $this->faker->boolean,
            'column' => $this->faker->word(),
            'value' => $this->faker->word(),
        ];
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'school_id' => School::current()->id,
            'name' => $this->faker->words(asText: true),
            'template' => [
                'invoice_column' => $this->faker->word(),
                'invoice_payment_term' => $this->mapField(),
                'payment_method' => $this->mapField(),
                'transaction_details' => $this->mapField(),
                'paid_at' => $this->mapField(),
                'amount' => $this->mapField(),
                'made_by' => $this->mapField(),
                'notes' => $this->mapField(),
            ],
        ];
    }
}
