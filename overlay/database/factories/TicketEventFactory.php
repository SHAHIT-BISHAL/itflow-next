<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketEventFactory extends Factory
{
    protected $model = \App\Models\TicketEvent::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'ticket_id' => Ticket::factory(),
            'actor_type' => null,
            'actor_id' => null,
            'event_type' => 'ticket.created',
            'description' => 'Ticket created.',
            'before' => null,
            'after' => null,
            'metadata' => null,
        ];
    }
}
