<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('model'); // fully-qualified model class this field applies to, e.g. App\Models\Client
            $table->string('label');
            $table->string('type')->default('text'); // text, textarea, number, date, select, checkbox
            $table->json('options')->nullable(); // for select-type fields
            $table->unsignedInteger('sort_order')->default(999);
            $table->timestamps();
        });

        Schema::create('custom_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_field_id')->constrained('custom_fields')->cascadeOnDelete();
            $table->morphs('customizable'); // customizable_type, customizable_id
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['custom_field_id', 'customizable_type', 'customizable_id'], 'custom_field_values_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_field_values');
        Schema::dropIfExists('custom_fields');
    }
};
