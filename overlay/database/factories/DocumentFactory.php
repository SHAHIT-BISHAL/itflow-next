<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFactory extends Factory
{
    protected $model = \App\Models\Document::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'client_id' => Client::factory(),
            'category_id' => null,
            'created_by' => User::factory(),
            'title' => $this->faker->sentence(3),
            'document_type' => 'runbook',
            'content' => $this->faker->paragraph(),
            'is_template' => false,
            'review_due_at' => null,
            'reviewed_at' => null,
            'reviewed_by' => null,
        ];
    }
}
