<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // When present for a user, restricts that user to only the listed clients.
        // No rows for a user = unrestricted access to all clients in their company.
        Schema::create('user_client_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'client_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_client_permissions');
    }
};
