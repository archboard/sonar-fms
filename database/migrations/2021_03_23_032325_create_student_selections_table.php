<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentSelectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_selections', function (Blueprint $table) {
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('student_uuid')->constrained('students', 'uuid')->onDelete('cascade');
            $table->foreignUuid('user_uuid')->constrained('users', 'uuid')->onDelete('cascade');
            $table->primary(['school_id', 'student_uuid', 'user_uuid']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_selections');
    }
}
