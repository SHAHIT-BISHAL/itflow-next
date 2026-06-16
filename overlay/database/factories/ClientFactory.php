<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    protected $model = \App\Models\Client::class;

    public function definition(): array
    {
        return [
            // company_id is auto-assigned from the authenticated user by
            // App\Models\Concerns\BelongsToCompany when not explicitly set.
            'name' => $this->faker->company(),
            'type' => 'Customer',
            'net_terms' => 30,
            'currency_code' => 'USD',
        ];
    }
}
