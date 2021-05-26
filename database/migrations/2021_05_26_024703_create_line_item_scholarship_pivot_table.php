<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLineItemScholarshipPivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_item_invoice_scholarship', function (Blueprint $table) {
            $table->uuid('invoice_item_uuid');
            $table->foreign('invoice_item_uuid')->references('uuid')->on('invoice_items')->onDelete('cascade');
            $table->uuid('invoice_scholarship_uuid');
            $table->foreign('invoice_scholarship_uuid')->references('uuid')->on('invoice_scholarships')->onDelete('cascade');
            $table->primary(['invoice_item_uuid', 'invoice_scholarship_uuid']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice_item_invoice_scholarship');
    }
}
