<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\RecordExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class RecordsExportTest extends TestCase
{
    use RefreshDatabase;

    protected bool $signIn = true;

    public function test_can_create_a_new_invoice_export()
    {
        $this->assignPermission('viewAny', Invoice::class);
        $data = [
            'name' => Str::random(),
            'format' => 'xlsx',
            'apply_filters' => true,
            'filters' => [
                's' => '',
                'status' => [],
                'date_start' => null,
                'date_end' => null,
            ],
        ];

        $this->post('/export/invoices', $data)
            ->assertRedirect();

        $data['school_id'] = $this->school->id;
        $data['user_uuid'] = $this->user->uuid;
        unset($data['filters']);
        $this->assertDatabaseHas('record_exports', $data);
    }

    public function test_cant_create_a_new_invoice_export_without_permission()
    {
        $data = [
            'name' => Str::random(),
            'format' => 'xlsx',
            'apply_filters' => true,
            'filters' => [
                's' => '',
                'status' => [],
                'date_start' => null,
                'date_end' => null,
            ],
        ];

        $this->post('/export/invoices', $data)
            ->assertForbidden();
    }

    public function test_can_download_invoice_export()
    {
        Excel::fake();

        $export = RecordExport::factory()->create(['user_uuid' => $this->user->uuid]);

        $this->get(route('exports.download', $export));

        Excel::assertDownloaded($export->file_name);
    }
}
