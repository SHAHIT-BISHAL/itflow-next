<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactFactory extends Factory
{
    protected $model = \App\Models\Contact::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'location_id' => null,
            'name' => $this->faker->name(),
            'title' => $this->faker->jobTitle(),
            'department' => null,
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'phone_extension' => null,
            'mobile' => null,
            'photo' => null,
            'is_primary' => false,
            'is_important' => false,
            'is_billing' => false,
            'is_technical' => false,
            'notes' => null,
        ];
    }
}
