<?php

namespace App\Factories;

use App\Models\InvoiceImport;

class InvoiceFromImportFactory extends InvoiceFactory
{
    protected ?InvoiceImport $import;

    public static function make(InvoiceImport $import = null): static
    {
        return (new static)
            ->setInvoiceImport($import);
    }

    public function setInvoiceImport(InvoiceImport $import = null): static
    {
        $this->import = $import;

        return $this;
    }
}
