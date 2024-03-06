<?php

namespace Tests\Unit;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TenantSyncEmailParseTest extends TestCase
{
    use WithFaker;

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_can_parse_emails_successfully()
    {
        $bank = [
            $this->faker->email,
            'invalid',
            $this->faker->email,
            $this->faker->email,
            $this->faker->email,
            'otherbad',
        ];

        $string = array_reduce($bank, function ($string, $email) {
            return $string.
                $email.
                $this->faker->randomElement([',', ';', '|', ' ']).
                $this->faker->randomElement([' ', '']);
        }, '');

        $emails = (new Tenant([
            'sync_notification_emails' => $string,
        ]))->getSyncNotificationEmails();

        $this->assertEquals(count($bank) - 2, count($emails));

        foreach ($emails as $email) {
            $this->assertTrue(in_array($email, $bank));
        }
    }
}
