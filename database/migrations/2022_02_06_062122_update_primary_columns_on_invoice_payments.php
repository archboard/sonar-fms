<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            Schema::table('invoice_payments', function (Blueprint $table) {
                $table->dropPrimary(['id']);
                $table->dropColumn(['id']);
                $table->primary(['uuid']);
            });
        } catch (\Illuminate\Database\QueryException $ex) {
            // ignore
            ray($ex->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->dropPrimary(['uuid']);
        });
    }
};
