<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentVersionFactory extends Factory
{
    protected $model = \App\Models\DocumentVersion::class;

    public function definition(): array
    {
        return [
            'document_id' => Document::factory(),
            'created_by' => User::factory(),
            'version_number' => 1,
            'title' => $this->faker->sentence(3),
            'content' => $this->faker->paragraph(),
            'change_summary' => 'Initial version',
        ];
    }
}
