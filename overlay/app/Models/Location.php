<?php

namespace App\Models;

use App\Models\Concerns\HasTags;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory, HasTags;

    protected $fillable = [
        'client_id', 'name', 'description', 'address', 'city', 'state', 'zip', 'country',
        'phone', 'phone_extension', 'hours', 'is_primary', 'is_favorite', 'notes',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_favorite' => 'boolean',
        'accessed_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }
}
