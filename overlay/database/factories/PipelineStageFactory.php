<?php

namespace Database\Factories;

use App\Models\Pipeline;
use Illuminate\Database\Eloquent\Factories\Factory;

class PipelineStageFactory extends Factory
{
    protected $model = \App\Models\PipelineStage::class;

    public function definition(): array
    {
        return [
            'pipeline_id' => Pipeline::factory(),
            'name' => $this->faker->randomElement(['Prospecting', 'Qualified', 'Proposal']),
            'color' => 'blue',
            'probability' => 25,
            'sort_order' => 0,
        ];
    }
}
