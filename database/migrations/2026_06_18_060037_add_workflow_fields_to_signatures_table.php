<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('signatures', function (Blueprint $table) {
            $table->string('status')->default('pending'); 
            $table->integer('signer_order')->default(1);
            $table->string('signer_email')->nullable();
            $table->string('ip_address')->nullable();
        });
    }

    public function down()
    {
        Schema::table('signatures', function (Blueprint $table) {
            $table->dropColumn(['status', 'signer_order', 'signer_email', 'ip_address']);
        });
    }
};