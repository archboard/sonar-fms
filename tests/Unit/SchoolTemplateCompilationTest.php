<?php

namespace Tests\Unit;

use App\Models\Student;
use Tests\TestCase;
use Tests\Traits\SeedsTerms;

class SchoolTemplateCompilationTest extends TestCase
{
    use SeedsTerms;

    public function test_can_correctly_convert_number_template()
    {
        $user = $this->signIn();
        $now = $user->getCarbonFactory()->now();

        $this->school->invoice_number_template = null;
        $this->assertEquals('', $this->school->getInvoiceNumberPrefix($this->user));

        $this->school->invoice_number_template = '{year}-';
        $this->assertEquals("{$now->format('Y')}-", $this->school->getInvoiceNumberPrefix($this->user));

        $this->school->invoice_number_template = '{year}{month}-';
        $this->assertEquals("{$now->format('Y')}{$now->format('m')}-", $this->school->getInvoiceNumberPrefix($this->user));

        $this->school->invoice_number_template = 'INV';
        $this->assertEquals('INV', $this->school->getInvoiceNumberPrefix($this->user));

        $this->school->invoice_number_template = '{year}{month}{month}{year}-INV';
        $this->assertEquals("{$now->format('Y')}{$now->format('m')}{$now->format('m')}{$now->format('Y')}-INV", $this->school->getInvoiceNumberPrefix($this->user));
    }

    public function test_can_compile_terms_without_providing_one()
    {
        $user = $this->signIn();
        $now = $user->getCarbonFactory()->now();
        $term = $this->seedTerm();

        $this->assertEquals(
            "{$now->year}{$term->abbreviation}-{$term->school_years}-{$term->next_school_years}",
            $this->school->compileTemplate('{year}{term}-{school_year}-{next_school_year}')
        );
    }

    public function test_can_compile_terms_with_providing_one()
    {
        $term = $this->seedTerm(['abbreviation' => 'S4']);

        $this->assertEquals(
            $term->abbreviation,
            $this->school->compileTemplate('{term}', term: $term)
        );
    }

    public function test_can_compile_with_student_details()
    {
        /** @var Student $student */
        $student = $this->school->students->random();

        $this->assertEquals(
            $student->student_number.$student->sis_id.$student->last_name.$student->first_name,
            $this->school->compileTemplate('{student_number}{sis_id}{last_name}{first_name}', student: $student)
        );
    }

    public function test_full_template_compilation()
    {
        $user = $this->createUser(['timezone' => 'America/Juneau']);
        $now = $user->getCarbonFactory()->now();
        $term = $this->seedTerm();
        /** @var Student $student */
        $student = $this->school->students->random();
        $d = $now->format('d');
        $m = $now->format('m');
        $y = $now->format('Y');

        $this->assertEquals(
            "{$m}{$student->last_name}{$student->sis_id}:{$student->first_name}{$y}{$student->first_name}{$d}{$d}{$term->school_years}{$student->student_number}{$term->abbreviation}--STATIC",
            $this->school->compileTemplate('{month}{last_name}{sis_id}:{first_name}{year}{first_name}{day}{day}{school_year}{student_number}{term}--STATIC', $user, $student, $term)
        );
    }
}
