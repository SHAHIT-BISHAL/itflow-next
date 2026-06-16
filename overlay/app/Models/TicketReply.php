<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
