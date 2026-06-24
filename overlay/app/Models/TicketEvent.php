<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int $company_id
 * @property int $ticket_id
 * @property string|null $actor_type
 * @property int|null $actor_id
 * @property string $event_type
 * @property string|null $description
 * @property array<string, mixed>|null $before
 * @property array<string, mixed>|null $after
 * @property array<string, mixed>|null $metadata
 * @property-read Ticket $ticket
 * @property-read Model|null $actor
 */
class TicketEvent extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'ticket_id',
        'actor_type',
        'actor_id',
        'event_type',
        'description',
        'before',
        'after',
        'metadata',
    ];

    protected $casts = [
        'before' => 'array',
        'after' => 'array',
        'metadata' => 'array',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function actor(): MorphTo
    {
        return $this->morphTo();
    }
}
