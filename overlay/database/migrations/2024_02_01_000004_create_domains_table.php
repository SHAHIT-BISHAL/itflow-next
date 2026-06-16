<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name'); // the domain name, e.g. example.com
            $table->string('registrar')->nullable();
            $table->date('expires_at')->nullable();
            $table->boolean('auto_renew')->default(false);
            $table->string('dns_provider')->nullable();
            // SSL certificate tracking
            $table->date('ssl_expires_at')->nullable();
            $table->string('ssl_issuer')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};
