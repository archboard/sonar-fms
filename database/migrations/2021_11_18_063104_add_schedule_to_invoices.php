<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddScheduleToInvoices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->uuid('invoice_payment_schedule_uuid')->nullable();
            $table->foreign('invoice_payment_schedule_uuid')->references('uuid')->on('invoice_payment_schedules')->onDelete('set null');
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
            $table->dropForeign(['invoice_payment_schedule_uuid']);
            $table->dropColumn('invoice_payment_schedule_uuid');
        });
    }
}
