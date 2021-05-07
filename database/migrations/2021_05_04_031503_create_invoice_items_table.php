<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_uuid')->index();
            $table->foreign('invoice_uuid')->references('uuid')->on('invoices')->onDelete('cascade');
            $table->unsignedBigInteger('fee_id')->nullable();
            $table->foreign('fee_id')->references('id')->on('fees')->onDelete('set null');
            $table->boolean('sync_with_fee')->default(false);
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('amount_per_unit')->nullable();
            $table->unsignedBigInteger('amount')->nullable();
            $table->integer('quantity')->default(1);
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
        Schema::dropIfExists('invoice_items');
    }
}
