<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class LocationFactory extends Factory
{
    protected $model = \App\Models\Location::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'name' => 'Head Office',
            'description' => null,
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->stateAbbr(),
            'zip' => $this->faker->postcode(),
            'country' => 'US',
            'phone' => $this->faker->phoneNumber(),
            'phone_extension' => null,
            'hours' => null,
            'is_primary' => true,
            'is_favorite' => false,
            'notes' => null,
        ];
    }
}
