<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class TenantFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Tenant::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            //
        ];
    }

    public function testing()
    {
        return $this->state([
            'name' => 'District',
            'domain' => parse_url(env('TESTING_APP_URL'))['host'],
            'ps_url' => env('POWERSCHOOL_ADDRESS'),
            'ps_client_id' => env('POWERSCHOOL_CLIENT_ID'),
            'ps_secret' => env('POWERSCHOOL_CLIENT_SECRET'),
            'license' => \Ramsey\Uuid\Uuid::uuid4(),
            'allow_password_auth' => false,
            'subscription_started_at' => now(),
        ]);
    }
}
