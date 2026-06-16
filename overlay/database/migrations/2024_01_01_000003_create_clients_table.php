<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->nullable(); // e.g. Customer, Lead, Prospect
            $table->boolean('is_lead')->default(false);
            $table->string('website')->nullable();
            $table->string('referral')->nullable();
            $table->decimal('rate', 15, 2)->nullable();
            $table->string('currency_code', 3)->default('USD');
            $table->unsignedInteger('net_terms')->default(30);
            $table->string('tax_id_number')->nullable();
            $table->string('abbreviation', 10)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_favorite')->default(false);
            $table->timestamp('accessed_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
