<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Company;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use Illuminate\Database\Eloquent\Factories\Factory;

class DealFactory extends Factory
{
    protected $model = \App\Models\Deal::class;

    public function definition(): array
    {
        $pipeline = Pipeline::factory();

        return [
            'company_id' => Company::factory(),
            'client_id' => Client::factory(),
            'contact_id' => null,
            'pipeline_id' => $pipeline,
            'stage_id' => PipelineStage::factory()->for($pipeline),
            'assigned_to' => null,
            'name' => $this->faker->company().' Project',
            'value' => 5000,
            'currency' => 'USD',
            'status' => 'open',
            'expected_close_date' => now()->addMonth()->toDateString(),
            'closed_at' => null,
            'lost_reason' => null,
            'notes' => null,
        ];
    }
}
