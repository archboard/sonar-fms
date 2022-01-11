<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPdfBatchIdToInvoiceImports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoice_imports', function (Blueprint $table) {
            $table->uuid('pdf_batch_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoice_imports', function (Blueprint $table) {
            $table->dropColumn('pdf_batch_id');
        });
    }
}
