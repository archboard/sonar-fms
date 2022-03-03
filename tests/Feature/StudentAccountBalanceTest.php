<?php

namespace Tests\Feature;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesInvoice;

class StudentAccountBalanceTest extends TestCase
{
    use RefreshDatabase;
    use CreatesInvoice;

    protected bool $signIn = true;
    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->student = $this->createStudent();
    }

    public function test_cant_update_balance_without_permission()
    {
        $this->put(route('students.balance', $this->student))
            ->assertForbidden();
    }

    public function test_can_set_account_balance()
    {
        $this->assignPermission('update', Student::class);
        $this->assertEquals(0, $this->student->account_balance);
        $invoice = $this->createInvoice(['student_uuid' => $this->student->uuid]);

        $this->put(route('students.balance', $this->student))
            ->assertSessionHas('success')
            ->assertRedirect();

        $this->student->refresh();
        $this->assertEquals($invoice->amount_due, $this->student->account_balance);
    }
}
