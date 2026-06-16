<?php

namespace Database\Factories;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceItemFactory extends Factory
{
    protected $model = \App\Models\InvoiceItem::class;

    public function definition(): array
    {
        $quantity = 1;
        $unitPrice = 100;

        return [
            'invoice_id' => Invoice::factory(),
            'description' => $this->faker->sentence(3),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'tax_rate' => 0,
            'amount' => $quantity * $unitPrice,
            'sort_order' => 0,
        ];
    }
}
