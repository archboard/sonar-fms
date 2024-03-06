<?php

namespace Tests\Feature;

use App\Notifications\TestSmtp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Tests\Traits\SignsIn;

class SmtpSettingsTest extends TestCase
{
    use RefreshDatabase;
    use SignsIn;

    public function test_cant_send_without_permission()
    {
        $this->post(route('smtp.test'))
            ->assertForbidden();
    }

    public function test_can_queue_test_mail()
    {
        $this->user->update(['manages_tenancy' => true]);
        Notification::fake();

        $this->postJson(route('smtp.test'))
            ->assertJsonStructure(['level', 'message']);

        Notification::assertSentTo($this->user, TestSmtp::class);
    }
}
