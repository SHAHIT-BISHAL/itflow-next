<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\HasTags;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory, BelongsToCompany, HasTags;

    protected $fillable = [
        'company_id', 'client_id', 'contact_id', 'assigned_to',
        'subject', 'status', 'priority', 'type', 'source',
        'email_message_id', 'sla_due_at', 'resolved_at', 'closed_at', 'archived_at',
    ];

    protected $casts = [
        'sla_due_at'  => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at'   => 'datetime',
        'archived_at' => 'datetime',
    ];

    public function client()      { return $this->belongsTo(Client::class); }
    public function contact()     { return $this->belongsTo(Contact::class); }
    public function assignee()    { return $this->belongsTo(User::class, 'assigned_to'); }
    public function replies()     { return $this->hasMany(TicketReply::class)->orderBy('created_at'); }
    public function latestReply() { return $this->hasOne(TicketReply::class)->latestOfMany(); }

    public function scopeActive($query)   { return $query->whereNull('archived_at'); }
    public function scopeOpen($query)     { return $query->whereIn('status', ['open', 'pending']); }
    public function scopeStatus($query, string $status) { return $query->where('status', $status); }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'urgent' => 'red',
            'high'   => 'yellow',
            'medium' => 'blue',
            'low'    => 'gray',
            default  => 'gray',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'open'     => 'blue',
            'pending'  => 'yellow',
            'resolved' => 'green',
            'closed'   => 'gray',
            default    => 'gray',
        };
    }
}
