<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurrenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('number')->nullable();
            $table->integer('digits')->default(0);
            $table->string('currency')->nullable();
            $table->timestamps();
        });

        Schema::table('schools', function (Blueprint $table) {
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->string('currency_symbol')->default('$');
            $table->integer('currency_decimals')->default(2);
            $table->dropForeign(['currency_id']);
            $table->dropColumn('currency_id');
        });

        Schema::dropIfExists('currencies');
    }
}
