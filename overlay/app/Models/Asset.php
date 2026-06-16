<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\HasTags;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory, BelongsToCompany, HasTags;

    protected $fillable = [
        'company_id', 'client_id', 'location_id', 'name', 'asset_type',
        'manufacturer', 'model', 'serial_number', 'ip_address', 'mac_address',
        'os', 'os_version', 'purchased_at', 'warranty_expires_at', 'notes', 'archived_at',
    ];

    protected $casts = [
        'purchased_at' => 'date',
        'warranty_expires_at' => 'date',
        'archived_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    public function scopeSearch($query, ?string $term)
    {
        return $query->when($term, fn ($q) => $q->where(function ($q2) use ($term) {
            $q2->where('name', 'like', "%{$term}%")
               ->orWhere('serial_number', 'like', "%{$term}%")
               ->orWhere('ip_address', 'like', "%{$term}%")
               ->orWhere('manufacturer', 'like', "%{$term}%");
        }));
    }

    public function getWarrantyStatusAttribute(): string
    {
        if (! $this->warranty_expires_at) {
            return 'unknown';
        }
        if ($this->warranty_expires_at->isPast()) {
            return 'expired';
        }
        if ($this->warranty_expires_at->diffInDays(now()) <= 0 && $this->warranty_expires_at->isFuture()) {
            return 'active';
        }
        return $this->warranty_expires_at->diffInDays(now()) <= 30 ? 'expiring' : 'active';
    }
}
