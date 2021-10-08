<?php

namespace Database\Factories;

use App\Factories\UuidFactory;
use App\Models\School;
use App\Models\Student;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Student::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'uuid' => UuidFactory::make(),
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->firstName,
            'email' => $this->faker->email,
            'sis_id' => $this->faker->numberBetween(1000, 9999),
            'student_number' => $this->faker->numberBetween(1000, 9999),
            'grade_level' => $this->faker->numberBetween(-2, 12),
            'enroll_status' => 0,
            'enrolled' => true,
//            'school_id' => function () {
//                return School::inRandomOrder()->first()->id;
//            },
//            'tenant_id' => function () {
//                return Tenant::first()->id;
//            },
        ];
    }

    public function unenrolled()
    {
        return $this->state([
            'enroll_status' => 1,
            'enrolled' => false,
        ]);
    }
}
