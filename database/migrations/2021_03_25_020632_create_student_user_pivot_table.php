<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentUserPivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_user', function (Blueprint $table) {
            $table->foreignUuid('student_uuid')->constrained('students', 'uuid')->onDelete('cascade');
            $table->foreignUuid('user_uuid')->constrained('users', 'uuid')->onDelete('cascade');
            $table->string('relationship')->nullable();
            $table->primary(['student_uuid', 'user_uuid']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_user');
    }
}
