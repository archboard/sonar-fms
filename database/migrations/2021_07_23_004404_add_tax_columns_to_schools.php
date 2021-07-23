<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTaxColumnsToSchools extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->boolean('collect_tax')->default(false);
            $table->decimal('tax_rate', 9, 8)->nullable();
            $table->string('tax_label')->nullable();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->boolean('use_school_tax_defaults')->default(true);
            $table->decimal('tax_rate', 9, 8)->nullable();
            $table->string('tax_label')->nullable();
            $table->unsignedBigInteger('tax_due')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'use_school_tax_defaults',
                'tax_rate',
                'tax_label',
                'tax_due',
            ]);
        });

        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn([
                'collect_tax',
                'tax_rate',
                'tax_label',
            ]);
        });
    }
}
