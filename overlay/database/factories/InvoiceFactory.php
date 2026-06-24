<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = \App\Models\Invoice::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'client_id' => Client::factory(),
            'contact_id' => null,
            'invoice_number' => strtoupper($this->faker->unique()->bothify('INV-####')),
            'status' => 'draft',
            'subtotal' => 0,
            'tax_amount' => 0,
            'total' => 0,
            'amount_paid' => 0,
            'currency' => 'USD',
            'issue_date' => today()->toDateString(),
            'due_date' => today()->addDays(30)->toDateString(),
            'notes' => null,
            'terms' => null,
            'sent_at' => null,
            'paid_at' => null,
        ];
    }
}
