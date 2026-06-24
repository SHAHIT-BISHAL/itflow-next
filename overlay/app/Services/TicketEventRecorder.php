<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class TicketEventRecorder
{
    /**
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     * @param  array<string, mixed>  $metadata
     */
    public static function record(
        Ticket $ticket,
        string $eventType,
        ?string $description = null,
        ?array $before = null,
        ?array $after = null,
        array $metadata = [],
    ): TicketEvent {
        $actor = Auth::user();
        $actorModel = $actor instanceof Model ? $actor : null;

        return TicketEvent::create([
            'company_id' => $ticket->company_id,
            'ticket_id' => $ticket->id,
            'actor_type' => $actorModel ? $actorModel::class : null,
            'actor_id' => $actorModel?->getKey(),
            'event_type' => $eventType,
            'description' => $description,
            'before' => $before,
            'after' => $after,
            'metadata' => $metadata ?: null,
        ]);
    }
}
