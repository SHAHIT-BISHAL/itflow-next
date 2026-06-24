<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomFieldFactory extends Factory
{
    protected $model = \App\Models\CustomField::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'model' => Client::class,
            'label' => $this->faker->words(2, true),
            'type' => 'text',
            'options' => null,
            'sort_order' => 0,
        ];
    }
}
