<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRollbackColumnsToInvoiceImports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoice_imports', function (Blueprint $table) {
            $table->dateTime('rolled_back_at')->nullable();
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
            $table->dropColumn('rolled_back_at');
        });
    }
}
