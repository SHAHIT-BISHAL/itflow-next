<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('timezone')->default('UTC');
            $table->string('default_currency', 3)->default('USD');
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->unsignedSmallInteger('default_net_terms')->default(30);
            $table->unsignedSmallInteger('ticket_sla_hours')->default(24);
            $table->json('business_hours')->nullable();
            $table->string('email_from_name')->nullable();
            $table->string('email_from_address')->nullable();
            $table->string('portal_name')->nullable();
            $table->string('portal_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
