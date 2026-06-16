<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class PipelineFactory extends Factory
{
    protected $model = \App\Models\Pipeline::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => 'Sales Pipeline',
            'is_default' => true,
            'sort_order' => 0,
        ];
    }
}
