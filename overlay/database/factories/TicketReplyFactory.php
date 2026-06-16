<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketReplyFactory extends Factory
{
    protected $model = \App\Models\TicketReply::class;

    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'user_id' => User::factory(),
            'contact_id' => null,
            'body' => $this->faker->paragraph(),
            'is_internal' => false,
            'source' => 'web',
            'email_message_id' => null,
        ];
    }
}
