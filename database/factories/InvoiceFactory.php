<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\School;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Ramsey\Uuid\Uuid;

class InvoiceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Invoice::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $school = School::current();

        return [
            'tenant_id' => Tenant::current()->id,
            'school_id' => $school->id,
            'uuid' => (string) Uuid::uuid4(),
            'title' => $this->faker->word,
            'description' => $this->faker->sentence,
            'invoice_date' => now(),
            'student_id' => $school->students()
                ->inRandomOrder()
                ->first()
                ->id,
        ];
    }
}
