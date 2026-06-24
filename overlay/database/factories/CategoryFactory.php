<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = \App\Models\Category::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'parent_id' => null,
            'name' => $this->faker->word(),
            'description' => null,
            'type' => 'general',
            'color' => 'gray',
            'icon' => null,
            'sort_order' => 0,
        ];
    }
}
