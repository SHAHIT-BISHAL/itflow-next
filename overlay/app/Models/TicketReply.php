<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketReply extends Model
{
    protected $fillable = [
        'ticket_id', 'user_id', 'contact_id',
        'body', 'is_internal', 'source', 'email_message_id',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
    ];

    public function ticket()  { return $this->belongsTo(Ticket::class); }
    public function user()    { return $this->belongsTo(User::class); }
    public function contact() { return $this->belongsTo(Contact::class); }
    public function attachments() { return $this->hasMany(TicketAttachment::class); }

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
