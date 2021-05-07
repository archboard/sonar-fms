<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique()->index();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('term_id')->nullable();
            $table->foreign('term_id')->references('id')->on('terms')->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('amount_due')->nullable();
            $table->unsignedBigInteger('remaining_balance')->nullable();
            $table->dateTime('due_at')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->dateTime('voided_at')->nullable();
            $table->boolean('notify')->default(false);
            $table->dateTime('notify_at')->nullable();
            $table->dateTime('notified_at')->nullable();
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
        Schema::dropIfExists('invoices');
    }
}
