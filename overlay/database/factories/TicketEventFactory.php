<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Ticket;
use App\Models\TicketEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketEventFactory extends Factory
{
    protected $model = \App\Models\TicketEvent::class;

    public function configure(): static
    {
        return $this->afterCreating(function (TicketEvent $event) {
            if ($event->ticket && (int) $event->company_id !== (int) $event->ticket->company_id) {
                $event->updateQuietly(['company_id' => $event->ticket->company_id]);
            }
        });
    }

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
