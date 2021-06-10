<?php

namespace Tests\Feature;

use App\Http\Resources\InvoiceTemplateResource;
use App\Models\InvoiceTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTemplateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }

    public function test_permissions_are_working()
    {
        $this->get(route('templates.index'))
            ->assertForbidden();
    }

    public function test_can_get_the_invoice_templates()
    {
        $this->assignPermission('viewAny', InvoiceTemplate::class);

        InvoiceTemplate::factory()->count(3)->create([
            'school_id' => $this->school->id,
        ]);

        $templates = $this->school->invoiceTemplates()
            ->orderBy('name')
            ->get();

        $this->get(route('templates.index'))
            ->assertOk()
            ->assertJson(InvoiceTemplateResource::collection($templates)->response()->getData(true));
    }

    public function test_can_create_a_new_template()
    {
        $this->assignPermission('create', InvoiceTemplate::class);

        $data = [
            'name' => 'My new template',
            'template' => [
                'title' => 'Title',
                'description' => 'Data'
            ],
        ];

        $this->post(route('templates.store'), $data)
            ->assertOk()
            ->assertJsonStructure(['level', 'message', 'data']);

        $this->assertEquals(1, $this->school->invoiceTemplates->count());

        /** @var InvoiceTemplate $template */
        $template = $this->school->invoiceTemplates->first();
        $this->assertEquals($data['name'], $template->name);
        $this->assertEquals($data['template'], $template->template);
    }

    public function test_can_update_existing_template()
    {
        $this->assignPermission('update', InvoiceTemplate::class);
        $template = InvoiceTemplate::factory()->create([
            'school_id' => $this->school->id,
        ]);

        $data = [
            'name' => 'My updated template',
            'template' => [
                'title' => 'Title',
                'description' => 'Data'
            ],
        ];

        $this->put(route('templates.update', $template), $data)
            ->assertOk()
            ->assertJsonStructure(['level', 'message', 'data']);

        $this->assertEquals(1, $this->school->invoiceTemplates->count());

        /** @var InvoiceTemplate $template */
        $template = $this->school->invoiceTemplates->first();
        $this->assertEquals($data['name'], $template->name);
        $this->assertEquals($data['template'], $template->template);
    }

    public function test_can_delete_template()
    {
        $this->assignPermission('delete', InvoiceTemplate::class);
        $template = InvoiceTemplate::factory()->create([
            'school_id' => $this->school->id,
        ]);

        $this->delete(route('templates.destroy', $template))
            ->assertOk()
            ->assertJsonStructure(['level', 'message']);

        $this->assertEquals(0, $this->school->invoiceTemplates->count());
    }
}
