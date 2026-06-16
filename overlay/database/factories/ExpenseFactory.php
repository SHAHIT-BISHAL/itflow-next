<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    protected $model = \App\Models\Expense::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'user_id' => User::factory(),
            'client_id' => Client::factory(),
            'category' => 'general',
            'description' => $this->faker->sentence(4),
            'amount' => 100,
            'currency' => 'USD',
            'vendor' => $this->faker->company(),
            'expense_date' => today()->toDateString(),
            'is_billable' => false,
            'invoiced_at' => null,
        ];
    }
}
