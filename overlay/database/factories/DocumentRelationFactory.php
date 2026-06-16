<?php

namespace Database\Factories;

use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentRelationFactory extends Factory
{
    protected $model = \App\Models\DocumentRelation::class;

    public function definition(): array
    {
        return [
            'document_id' => Document::factory(),
            'related_type' => \App\Models\Asset::class,
            'related_id' => \App\Models\Asset::factory(),
            'relationship_type' => 'reference',
            'notes' => null,
        ];
    }
}
