<?php

namespace App\Events;

use App\Models\InvoiceImport;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvoiceImportFinished
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public InvoiceImport $import;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(InvoiceImport $import)
    {
        $this->import = $import;
    }
}
