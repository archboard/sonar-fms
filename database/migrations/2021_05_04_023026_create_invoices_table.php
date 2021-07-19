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
            // This is just used as the invoice number
            $table->id();
            $table->uuid('uuid')->unique()->index();
            // Used for batch creations, e.g. creating an invoice from the student selection
            $table->string('batch_id')->index()->nullable();
            // Used for tracking imports via excel/csv
            $table->string('import_id')->index()->nullable();
            $table->foreignId('tenant_id')->index()->constrained()->onDelete('cascade');
            $table->foreignId('school_id')->index()->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->index()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->index()->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('term_id')->nullable();
            $table->foreign('term_id')->references('id')->on('terms')->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('amount_due')->nullable();
            $table->unsignedBigInteger('remaining_balance')->nullable();
            $table->unsignedBigInteger('subtotal')->nullable();
            $table->unsignedBigInteger('discount_total')->nullable();
            $table->date('invoice_date');
            $table->dateTime('available_at')->nullable();
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
