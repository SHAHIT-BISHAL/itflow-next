<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\HasTags;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $company_id
 * @property int|null $client_id
 * @property int|null $contact_id
 * @property int|null $assigned_to
 * @property string $subject
 * @property string $status
 * @property string $priority
 * @property string $type
 * @property string $source
 * @property string|null $email_message_id
 * @property \Illuminate\Support\Carbon|null $sla_due_at
 * @property \Illuminate\Support\Carbon|null $resolved_at
 * @property \Illuminate\Support\Carbon|null $closed_at
 * @property \Illuminate\Support\Carbon|null $archived_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Client|null $client
 * @property-read Contact|null $contact
 * @property-read User|null $assignee
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TicketReply> $replies
 * @property-read TicketReply|null $latestReply
 * @property-read string $priority_color
 * @property-read string $status_color
 */
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

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function contact(): BelongsTo { return $this->belongsTo(Contact::class); }
    public function assignee(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
    public function replies(): HasMany { return $this->hasMany(TicketReply::class)->orderBy('created_at'); }
    public function latestReply(): HasOne { return $this->hasOne(TicketReply::class)->latestOfMany(); }

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
