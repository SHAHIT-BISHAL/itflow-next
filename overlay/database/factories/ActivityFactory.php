<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Company;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityFactory extends Factory
{
    protected $model = \App\Models\Activity::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'user_id' => User::factory(),
            'deal_id' => null,
            'client_id' => Client::factory(),
            'contact_id' => null,
            'type' => 'note',
            'subject' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'due_at' => null,
            'completed_at' => null,
            'outcome' => null,
        ];
    }

    public function forDeal(Deal $deal): static
    {
        return $this->state(fn () => [
            'company_id' => $deal->company_id,
            'deal_id' => $deal->id,
            'client_id' => $deal->client_id,
            'contact_id' => $deal->contact_id,
        ]);
    }
}
