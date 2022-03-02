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
        Schema::create('record_exports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->uuid('user_uuid');
            $table->string('name');
            $table->string('format');
            $table->boolean('apply_filters');
            $table->json('filters');
            $table->string('model');
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
        Schema::dropIfExists('record_exports');
    }
};
