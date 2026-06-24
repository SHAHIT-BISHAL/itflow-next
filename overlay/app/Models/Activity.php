<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $company_id
 * @property int|null $user_id
 * @property int|null $deal_id
 * @property int|null $client_id
 * @property int|null $contact_id
 * @property string $type
 * @property string $subject
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $due_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property string|null $outcome
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User|null $user
 * @property-read Deal|null $deal
 * @property-read Client|null $client
 * @property-read Contact|null $contact
 * @property-read bool $is_completed
 * @property-read string $type_icon
 * @property-read string $type_color
 */
class Activity extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id', 'user_id', 'deal_id', 'client_id', 'contact_id',
        'type', 'subject', 'description', 'due_at', 'completed_at', 'outcome',
    ];

    protected $casts = [
        'due_at'       => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function deal(): BelongsTo { return $this->belongsTo(Deal::class); }
    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function contact(): BelongsTo { return $this->belongsTo(Contact::class); }

    public function getIsCompletedAttribute(): bool { return (bool) $this->completed_at; }

    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'call'    => 'phone',
            'email'   => 'envelope',
            'meeting' => 'calendar',
            'task'    => 'check-circle',
            default   => 'document-text',
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'call'    => 'green',
            'email'   => 'blue',
            'meeting' => 'purple',
            'task'    => 'yellow',
            default   => 'gray',
        };
    }
}
