<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = \App\Models\Payment::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'client_id' => Client::factory(),
            'invoice_id' => Invoice::factory(),
            'amount' => 100,
            'currency' => 'USD',
            'method' => 'bank_transfer',
            'reference' => $this->faker->bothify('REF-####'),
            'paid_at' => today()->toDateString(),
            'notes' => null,
        ];
    }
}
