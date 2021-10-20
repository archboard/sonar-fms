<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\School;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class StarterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(CurrencySeeder::class);
        $this->call(PaymentMethodSeeder::class);

        $tenant = Tenant::updateOrCreate(
            ['license' => '626d5b58-b541-453f-8072-32c6c709cb60'],
            [
                'name' => 'iSC',
                'domain' => 'demo.invoicing.test',
                'ps_url' => 'https://pstest.iscglobal.org',
                'ps_client_id' => 'ca40c46e-48e9-48a0-89d4-0f2311f4d7fa',
                'ps_secret' => '0294fbd8-cbea-4836-959e-7872c518f302',
                'allow_oidc_login' => true,
                'allow_password_auth' => true,
                'smtp_host' => '127.0.0.1',
                'smtp_port' => '2525',
                'smtp_username' => 'Sonar FMS',
                'smtp_from_name' => 'Sonar FMS',
                'smtp_from_address' => 'notifications@sonarfms.app',
            ]
        );

        $tis = School::updateOrCreate(
            ['school_number' => 400],
            [
                'tenant_id' => $tenant->id,
                'sis_id' => 6,
                'name' => 'Tianjin International School',
                'high_grade' => 12,
                'low_grade' => -2,
                'active' => true,
                'currency_id' => Currency::where('code', 'CNY')->first()->id,
                'timezone' => 'Asia/Shanghai',
            ],
        );

        $grant = User::updateOrCreate(
            ['email' => 'grant.holle@ldi.global'],
            [
                'uuid' => '26346f66-c412-4edb-8980-c73475ccbf3b',
                'tenant_id' => $tenant->id,
                'school_id' => $tis->id,
                'sis_id' => 5853,
                'first_name' => 'Grant',
                'last_name' => 'Holle',
                'password' => bcrypt('secret'),
                'contact_id' => 65283,
                'guardian_id' => 578000,
                'manages_tenancy' => true,
                'locale' => 'en',
            ]
        );

        $grant->schools()->attach($tis);
    }
}
