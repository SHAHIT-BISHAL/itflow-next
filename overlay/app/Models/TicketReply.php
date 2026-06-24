<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $ticket_id
 * @property int|null $user_id
 * @property int|null $contact_id
 * @property string $body
 * @property bool $is_internal
 * @property string $source
 * @property string|null $email_message_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Ticket $ticket
 * @property-read User|null $user
 * @property-read Contact|null $contact
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TicketAttachment> $attachments
 * @property-read string $author_name
 * @property-read bool $is_staff
 */
class TicketReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id', 'user_id', 'contact_id',
        'body', 'is_internal', 'source', 'email_message_id',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
    ];

    public function ticket(): BelongsTo { return $this->belongsTo(Ticket::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function contact(): BelongsTo { return $this->belongsTo(Contact::class); }
    public function attachments(): HasMany { return $this->hasMany(TicketAttachment::class); }

    public function getAuthorNameAttribute(): string
    {
        if ($this->user_id) return $this->user?->name ?? 'Staff';
        if ($this->contact_id) return $this->contact?->name ?? 'Contact';
        return 'Unknown';
    }

    public function getIsStaffAttribute(): bool
    {
        return (bool) $this->user_id;
    }
}
