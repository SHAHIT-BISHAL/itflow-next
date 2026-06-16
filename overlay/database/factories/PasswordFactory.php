<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class PasswordFactory extends Factory
{
    protected $model = \App\Models\Password::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'client_id' => Client::factory(),
            'name' => $this->faker->words(2, true),
            'username' => $this->faker->userName(),
            'password' => 'secret',
            'url' => $this->faker->url(),
            'notes' => null,
        ];
    }
}
