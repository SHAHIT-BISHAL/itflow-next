<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    protected $model = \App\Models\Company::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'currency' => 'USD',
            'timezone' => 'UTC',
        ];
    }
}
