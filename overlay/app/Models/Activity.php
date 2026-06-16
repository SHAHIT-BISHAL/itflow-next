<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function user()    { return $this->belongsTo(User::class); }
    public function deal()    { return $this->belongsTo(Deal::class); }
    public function client()  { return $this->belongsTo(Client::class); }
    public function contact() { return $this->belongsTo(Contact::class); }

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
