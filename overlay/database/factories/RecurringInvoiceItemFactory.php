<?php

namespace Database\Factories;

use App\Models\RecurringInvoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecurringInvoiceItemFactory extends Factory
{
    protected $model = \App\Models\RecurringInvoiceItem::class;

    public function definition(): array
    {
        return [
            'recurring_invoice_id' => RecurringInvoice::factory(),
            'description' => $this->faker->sentence(3),
            'quantity' => 1,
            'unit_price' => 100,
            'tax_rate' => 0,
            'sort_order' => 0,
        ];
    }
}
