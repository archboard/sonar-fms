<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvoiceUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_user', function (Blueprint $table) {
            $table->foreignUuid('invoice_uuid')->constrained('invoices', 'uuid')->onDelete('cascade');
            $table->foreignUuid('user_uuid')->constrained('users', 'uuid')->onDelete('cascade');
            $table->primary(['invoice_uuid', 'user_uuid']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice_user');
    }
}
