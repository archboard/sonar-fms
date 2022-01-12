<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixImportIdColumnInInvoices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['import_id']);
            $table->dropColumn('import_id');
            $table->unsignedBigInteger('invoice_import_id')->nullable();
            $table->foreign('invoice_import_id')->references('id')->on('invoice_imports')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('import_id')->index()->nullable();
            $table->dropForeign(['invoice_import_id']);
            $table->dropColumn('invoice_import_id');
        });
    }
}
