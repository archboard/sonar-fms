<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicePaymentTermsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_payment_terms', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->uuid('invoice_uuid')->index()->nullable();
            $table->foreign('invoice_uuid')->references('uuid')->on('invoices')->onDelete('cascade');
            $table->uuid('invoice_payment_schedule_uuid')->index()->nullable();
            $table->foreign('invoice_payment_schedule_uuid')->references('uuid')->on('invoice_payment_schedules')->onDelete('cascade');
            $table->uuid('batch_id')->index()->nullable();
            $table->unsignedBigInteger('amount');
            $table->dateTime('due_at')->nullable();
            $table->dateTime('notified_at')->nullable();
            $table->boolean('notify')->default(false);
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
        Schema::dropIfExists('invoice_payment_terms');
    }
}
