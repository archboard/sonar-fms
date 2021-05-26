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
            $table->foreignId('invoice_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('invoice_scholarship_id')->constrained()->onDelete('cascade');
            $table->primary(['invoice_item_id', 'invoice_scholarship_id']);
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
