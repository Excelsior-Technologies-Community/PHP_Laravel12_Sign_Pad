<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('signature_requests', function (Blueprint $table) {
            $table->id();

            // Public-facing identifier used in shareable links so we never
            // expose the raw auto-increment id to signers.
            $table->uuid('uuid')->unique();

            // Human readable name of the document/contract being signed.
            $table->string('title');

            // Optional path to a source document (contract PDF, etc.) if you
            // want to attach one later. Not required for the pad to work.
            $table->string('document_filename')->nullable();

            // Who created/initiated this signing request.
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // pending   -> still waiting on one or more signers
            // completed -> every signer has signed, in order
            $table->string('status')->default('pending');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('signature_requests');
    }
};