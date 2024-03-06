<?php

namespace Tests\Feature;

use App\Models\PaymentImport;
use App\Models\PaymentImportTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\CreatesPaymentImports;
use Tests\Traits\GetsUploadedFiles;
use Tests\Traits\MapsFields;

class ConvertPaymentImportToTemplateTest extends TestCase
{
    use CreatesPaymentImports;
    use GetsUploadedFiles;
    use MapsFields;
    use RefreshDatabase;
    use WithFaker;

    protected bool $signIn = true;

    public function test_can_convert_import_to_template()
    {
        $this->assignPermission('create', PaymentImport::class);

        $import = $this->createImport();

        $import->mapping = [
            'invoice_column' => 'invoice number',
            'invoice_payment_term' => $this->makeMapField(),
            'payment_method' => $this->makeMapField('payment method'),
            'transaction_details' => $this->makeMapField('transaction details'),
            'paid_at' => $this->makeMapField('date'),
            'amount' => $this->makeMapField('amount'),
            'made_by' => $this->makeMapField('paid by'),
            'notes' => $this->makeMapField('notes'),
        ];
        $import->save();

        $this->post(route('payments.imports.template', $import), ['name' => 'new template'])
            ->assertSessionHas('success')
            ->assertRedirect();

        $this->assertDatabaseHas('payment_import_templates', [
            'user_uuid' => $this->user->uuid,
            'school_id' => $this->school->id,
            'name' => 'new template',
        ]);
        $template = PaymentImportTemplate::first();
        $this->assertEquals($import->mapping, $template->template);
    }
}
