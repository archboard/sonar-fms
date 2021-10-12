<?php

namespace Tests\Unit;

use Tests\TestCase;

class SchoolInvoiceNumberPrefixTest extends TestCase
{
    public function test_can_correctly_convert_number_template()
    {
        $user = $this->signIn();
        $now = $user->getCarbonFactory()->now();

        $this->school->invoice_number_template = null;
        $this->assertEquals("", $this->school->getInvoiceNumberPrefix($this->user));

        $this->school->invoice_number_template = '{year}-';
        $this->assertEquals("{$now->format('Y')}-", $this->school->getInvoiceNumberPrefix($this->user));

        $this->school->invoice_number_template = '{year}{month}-';
        $this->assertEquals("{$now->format('Y')}{$now->format('m')}-", $this->school->getInvoiceNumberPrefix($this->user));

        $this->school->invoice_number_template = 'INV';
        $this->assertEquals("INV", $this->school->getInvoiceNumberPrefix($this->user));

        $this->school->invoice_number_template = '{year}{month}{month}{year}-INV';
        $this->assertEquals("{$now->format('Y')}{$now->format('m')}{$now->format('m')}{$now->format('Y')}-INV", $this->school->getInvoiceNumberPrefix($this->user));
    }
}
