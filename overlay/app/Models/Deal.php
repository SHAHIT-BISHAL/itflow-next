<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'client_id', 'contact_id', 'pipeline_id', 'stage_id',
        'assigned_to', 'name', 'value', 'currency', 'status',
        'expected_close_date', 'closed_at', 'lost_reason', 'notes', 'archived_at',
    ];

    protected $casts = [
        'value'               => 'decimal:2',
        'expected_close_date' => 'date',
        'closed_at'           => 'datetime',
        'archived_at'         => 'datetime',
    ];

    public function pipeline()   { return $this->belongsTo(Pipeline::class); }
    public function stage()      { return $this->belongsTo(PipelineStage::class, 'stage_id'); }
    public function client()     { return $this->belongsTo(Client::class); }
    public function contact()    { return $this->belongsTo(Contact::class); }
    public function assignee()   { return $this->belongsTo(User::class, 'assigned_to'); }
    public function activities() { return $this->hasMany(Activity::class)->orderBy('created_at', 'desc'); }

    public function scopeActive($query) { return $query->whereNull('archived_at'); }
    public function scopeOpen($query)   { return $query->where('status', 'open'); }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'won'  => 'green',
            'lost' => 'red',
            default => 'blue',
        };
    }

    public function getFormattedValueAttribute(): string
    {
        return number_format($this->value, 2);
    }
}
