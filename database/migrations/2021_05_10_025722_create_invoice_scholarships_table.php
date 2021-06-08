<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceScholarshipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_scholarships', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->uuid('invoice_uuid')->index();
            $table->foreign('invoice_uuid')->references('uuid')->on('invoices')->onDelete('cascade');
            $table->uuid('batch_id')->index()->nullable();
            $table->unsignedBigInteger('scholarship_id')->nullable();
            $table->foreign('scholarship_id')->references('id')->on('scholarships')->onDelete('set null');
            $table->string('name');
            $table->decimal('percentage', 8, 4)->nullable();
            $table->unsignedBigInteger('amount')->nullable();
            $table->string('resolution_strategy')->nullable();
            $table->unsignedBigInteger('calculated_amount')->nullable();
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
        Schema::dropIfExists('invoice_scholarships');
    }
}
