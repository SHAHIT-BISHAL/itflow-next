<?php

namespace App\Models;

use App\Models\Concerns\HasTags;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }
}
