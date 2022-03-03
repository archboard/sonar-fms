<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Tests\Traits\CreatesInvoice;
use Tests\Traits\CreatesPayments;

class StudentRevenueTest extends TestCase
{
    use RefreshDatabase;
    use CreatesInvoice;
    use CreatesPayments;

    protected bool $signIn = true;
    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->student = $this->createStudent();
    }

    public function test_cant_update_balance_without_permission()
    {
        $this->put(route('students.revenue', $this->student))
            ->assertForbidden();
    }

    public function test_can_set_account_balance()
    {
        Queue::fake();
        $this->assignPermission('update', Student::class);
        $this->assertEquals(0, $this->student->account_balance);
        $invoice = $this->createInvoice(['student_uuid' => $this->student->uuid]);
        $payment = $this->createPayment(invoice: $invoice);

        $invoice = Invoice::find($invoice->uuid);
        $invoice->recordPayment($payment);

        $this->put(route('students.revenue', $this->student))
            ->assertSessionHas('success')
            ->assertRedirect();

        $this->student->refresh();
        $this->assertEquals($payment->amount, $this->student->revenue);
    }
}
