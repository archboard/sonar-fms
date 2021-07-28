<?php

namespace Database\Factories;

use App\Models\InvoicePaymentSchedule;
use App\Models\InvoicePaymentTerm;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InvoicePaymentScheduleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = InvoicePaymentSchedule::class;

    public function configure()
    {
        return $this->afterMaking(function (InvoicePaymentSchedule $schedule) {
            //
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
        ];
    }
}
