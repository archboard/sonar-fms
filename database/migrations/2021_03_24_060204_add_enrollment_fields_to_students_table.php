<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEnrollmentFieldsToStudentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('preferred_name')->nullable();
            $table->date('current_entry_date')->nullable();
            $table->date('current_exit_date')->nullable();
            $table->date('initial_district_entry_date')->nullable();
            $table->date('initial_school_entry_date')->nullable();
            $table->string('initial_district_grade_level')->nullable();
            $table->string('initial_school_grade_level')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'preferred_name',
                'current_entry_date',
                'current_exit_date',
                'initial_district_entry_date',
                'initial_school_entry_date',
                'initial_district_grade_level',
                'initial_school_grade_level',
            ]);
        });
    }
}
