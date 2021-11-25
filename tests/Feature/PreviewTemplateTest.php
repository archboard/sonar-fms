<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PreviewTemplateTest extends TestCase
{
    protected bool $signIn = true;

    public function test_can_get_template_preview()
    {
        $template = '{student_number}-{day}-{term}';

        $compiled = $this->post(route('preview.template'), compact('template'))
            ->assertOk()
            ->assertJsonStructure(['compiled'])
            ->json('compiled');

        $this->assertStringNotContainsString('{student_number}', $compiled);
        $this->assertStringNotContainsString('{day}', $compiled);
        $this->assertStringNotContainsString('{term}', $compiled);
    }
}
