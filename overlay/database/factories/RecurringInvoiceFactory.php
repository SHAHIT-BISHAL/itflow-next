<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecurringInvoiceFactory extends Factory
{
    protected $model = \App\Models\RecurringInvoice::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'client_id' => Client::factory(),
            'frequency' => 'monthly',
            'next_run_at' => today()->addMonth()->toDateString(),
            'last_run_at' => null,
            'is_active' => true,
            'notes' => null,
            'currency' => 'USD',
            'net_terms' => 30,
        ];
    }
}
