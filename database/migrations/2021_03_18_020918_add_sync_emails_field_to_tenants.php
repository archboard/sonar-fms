<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSyncEmailsFieldToTenants extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->text('sync_notification_emails')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('sync_notification_emails');
        });
    }
}
