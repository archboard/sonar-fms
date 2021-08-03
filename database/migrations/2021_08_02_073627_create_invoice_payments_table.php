<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->uuid('invoice_uuid')->index();
            $table->foreign('invoice_uuid')->references('uuid')->on('invoices')->onDelete('cascade');
            $table->uuid('invoice_payment_term_uuid')->index()->nullable();
            $table->foreign('invoice_payment_term_uuid')->references('uuid')->on('invoice_payment_terms')->onDelete('set null');
            $table->unsignedBigInteger('payment_method_id')->nullable();
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onDelete('set null');
            $table->dateTime('paid_at')->nullable();
            $table->unsignedBigInteger('amount')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->foreign('recorded_by')->references('id')->on('users')->onDelete('set null');
            $table->unsignedBigInteger('made_by')->nullable();
            $table->foreign('made_by')->references('id')->on('users')->onDelete('set null');
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
        Schema::dropIfExists('invoice_payments');
    }
}
