<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    protected $model = \App\Models\Ticket::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'client_id' => Client::factory(),
            'contact_id' => null,
            'assigned_to' => null,
            'subject' => $this->faker->sentence(5),
            'status' => 'open',
            'priority' => 'medium',
            'type' => 'general',
            'source' => 'web',
            'email_message_id' => null,
            'sla_due_at' => null,
            'resolved_at' => null,
            'closed_at' => null,
        ];
    }
}
