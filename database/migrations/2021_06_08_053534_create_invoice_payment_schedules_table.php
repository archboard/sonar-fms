<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicePaymentSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_payment_schedules', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->index()->primary();
            $table->uuid('invoice_uuid')->index()->nullable();
            $table->foreign('invoice_uuid')->references('uuid')->on('invoices')->onDelete('cascade');
            $table->uuid('batch_id')->index()->nullable();
            $table->unsignedBigInteger('amount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice_payment_schedules');
    }
}
