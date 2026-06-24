<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Company;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TimeEntryFactory extends Factory
{
    protected $model = TimeEntry::class;

    public function definition(): array
    {
        return [
            'company_id'   => Company::factory(),
            'ticket_id'    => null,
            'user_id'      => User::factory(),
            'client_id'    => null,
            'description'  => $this->faker->sentence(),
            'minutes'      => $this->faker->numberBetween(15, 240),
            'performed_at' => today(),
            'is_billable'  => true,
            'rate'         => null,
            'invoice_id'   => null,
            'invoiced_at'  => null,
        ];
    }

    public function nonBillable(): static
    {
        return $this->state(fn () => ['is_billable' => false]);
    }
}
