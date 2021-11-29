<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentImportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->uuid('user_uuid')->nullable();
            $table->foreign('user_uuid')->references('uuid')->on('users')->onDelete('set null');
            $table->string('file_path');
            $table->integer('heading_row')->default(1);
            $table->integer('starting_row')->default(2);
            $table->json('mapping')->nullable();
            $table->boolean('mapping_valid')->default(false);
            $table->integer('total_records')->nullable();
            $table->integer('imported_records')->default(0);
            $table->integer('failed_records')->default(0);
            $table->dateTime('imported_at')->nullable();
            $table->json('results')->nullable();
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
        Schema::dropIfExists('payment_imports');
    }
}
