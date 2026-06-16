<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->string('name');
            $table->string('title')->nullable();
            $table->string('department')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone_extension', 10)->nullable();
            $table->string('mobile')->nullable();
            $table->string('photo')->nullable();
            $table->string('password')->nullable(); // client-portal login (Phase 1: foundation only)
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_important')->default(false);
            $table->boolean('is_billing')->default(false);
            $table->boolean('is_technical')->default(false);
            $table->text('notes')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('accessed_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
