<?php

namespace Tests\Feature;

use App\Models\Currency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Inertia\Testing\Assert;
use Tests\TestCase;

class SchoolSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }

    public function test_cannot_get_to_settings_without_permission()
    {
        $this->get(route('settings.school'))
            ->assertForbidden();
    }

    public function test_can_see_settings_page()
    {
        $this->user->allow('edit school settings');

        $this->get(route('settings.school'))
            ->assertOk()
            ->assertViewHas('title')
            ->assertInertia(fn (Assert $assert) => $assert
                ->has('title')
                ->has('school')
                ->has('currencies')
                ->component('settings/School')
            );
    }

    public function test_can_save_school_settings()
    {
        $this->withoutExceptionHandling();
        $this->user->allow('edit school settings');
        $data = [
            'currency_id' => Currency::where('id', '!=', $this->school->currency_id)->inRandomOrder()->pluck('id')->first(),
            'timezone' => 'Antarctica/Troll',
            'collect_tax' => true,
            'tax_rate' => '8',
            'tax_label' => 'Nunya',
            'invoice_number_template' => 'INV--',
        ];

        $this->post(route('settings.school'), $data)
            ->assertRedirect()
            ->assertSessionHas('success');

        $data['id'] = $this->school->id;
        $data['tax_rate'] = '0.08000000';
        $this->assertDatabaseHas('schools', $data);
    }
}
