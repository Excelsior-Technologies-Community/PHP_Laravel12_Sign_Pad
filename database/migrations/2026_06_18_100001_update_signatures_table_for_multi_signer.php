<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Step 1: Add request_id and signed_at columns
        Schema::table('signatures', function (Blueprint $table) {
            $table->foreignId('request_id')
                ->nullable()
                ->after('id')
                ->constrained('signature_requests') 
                ->onDelete('cascade');

            $table->timestamp('signed_at')->nullable()->after('status');
        });

        // Step 2: Make filename nullable for multi-signer workflow
        Schema::table('signatures', function (Blueprint $table) {
            $table->string('filename')->nullable()->change();
        });
    }

    public function down()
    {
        // Step 1: Revert column changes
        Schema::table('signatures', function (Blueprint $table) {
            $table->dropForeign(['request_id']);
            $table->dropColumn(['request_id', 'signed_at']);
        });

        // Step 2: Revert filename to not nullable
        Schema::table('signatures', function (Blueprint $table) {
            $table->string('filename')->nullable(false)->change();
        });
    }
};