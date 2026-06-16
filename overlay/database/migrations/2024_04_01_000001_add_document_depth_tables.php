<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('document_type')->default('general')->after('title');
            $table->date('review_due_at')->nullable()->after('is_template');
            $table->timestamp('reviewed_at')->nullable()->after('review_due_at');
            $table->foreignId('reviewed_by')->nullable()->after('reviewed_at')
                ->constrained('users')->nullOnDelete();

            $table->index(['company_id', 'document_type']);
            $table->index(['company_id', 'review_due_at']);
        });

        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('title');
            $table->longText('content')->nullable();
            $table->string('change_summary')->nullable();
            $table->timestamps();

            $table->unique(['document_id', 'version_number']);
        });

        Schema::create('document_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->morphs('related');
            $table->string('relationship_type')->default('reference');
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['document_id', 'related_type', 'related_id'], 'document_relations_unique');
        });

        Schema::create('password_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('password_id')->nullable()->constrained('passwords')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 50);
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('accessed_at');
            $table->timestamps();

            $table->index(['company_id', 'accessed_at']);
            $table->index(['password_id', 'accessed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_access_logs');
        Schema::dropIfExists('document_relations');
        Schema::dropIfExists('document_versions');

        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'document_type']);
            $table->dropIndex(['company_id', 'review_due_at']);
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['document_type', 'review_due_at', 'reviewed_at']);
        });
    }
};
