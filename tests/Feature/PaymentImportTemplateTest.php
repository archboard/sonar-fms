<?php

namespace Tests\Feature;

use App\Models\PaymentImport;
use App\Models\PaymentImportTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PaymentImportTemplateTest extends TestCase
{
    use RefreshDatabase;

    protected bool $signIn = true;

    public function test_can_get_templates()
    {
        $templates = PaymentImportTemplate::factory()
            ->count(5)
            ->create(['user_uuid' => $this->user->uuid]);

        $data = $this->get(route('payments.imports.templates.index'))
            ->assertOk()
            ->json();

        $this->assertCount($templates->count(), $data);
    }

    public function test_can_create_new_template()
    {
        $this->assignPermission('create', PaymentImport::class);
        $template = PaymentImportTemplate::factory()->make()->template;

        $data = [
            'name' => 'My template',
            'template' => $template,
        ];

        $this->post(route('payments.imports.templates.store'), $data)
            ->assertOk()
            ->assertJsonStructure(['level', 'message', 'data']);

        $this->assertEquals(1, $this->user->paymentImportTemplates()->count());
        $template = $this->user->paymentImportTemplates()->first();

        $this->assertEquals($data['template'], $template->template);
    }

    public function test_can_update_existing_template()
    {
        $this->assignPermission('create', PaymentImport::class);
        /** @var PaymentImportTemplate $template */
        $template = PaymentImportTemplate::factory()
            ->create(['user_uuid' => $this->user->uuid]);

        /** @var PaymentImportTemplate $newTemplate */
        $newTemplate = PaymentImportTemplate::factory()->make();

        $data = [
            'name' => $newTemplate->name,
            'template' => $newTemplate->template,
        ];

        $this->put(route('payments.imports.templates.update', $template), $data)
            ->assertOk()
            ->assertJsonStructure(['level', 'message', 'data']);

        $template->refresh();
        $this->assertEquals($data['name'], $template->name);
        $this->assertEquals($data['template'], $template->template);
    }

    public function test_can_delete_template()
    {
        $this->assignPermission('create', PaymentImport::class);
        /** @var PaymentImportTemplate $template */
        $template = PaymentImportTemplate::factory()
            ->create(['user_uuid' => $this->user->uuid]);

        $this->delete(route('payments.imports.templates.destroy', $template))
            ->assertOk()
            ->assertJsonStructure(['level', 'message']);

        $this->assertModelMissing($template);
    }
}
