<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id', 'client_id', 'name', 'registrar', 'expires_at', 'auto_renew',
        'dns_provider', 'ssl_expires_at', 'ssl_issuer', 'notes', 'archived_at',
    ];

    protected $casts = [
        'expires_at' => 'date',
        'ssl_expires_at' => 'date',
        'auto_renew' => 'boolean',
        'archived_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    public function scopeSearch($query, ?string $term)
    {
        return $query->when($term, fn ($q) => $q->where(function ($q2) use ($term) {
            $q2->where('name', 'like', "%{$term}%")
               ->orWhere('registrar', 'like', "%{$term}%");
        }));
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->whereNotNull('expires_at')
                     ->where('expires_at', '<=', now()->addDays($days))
                     ->where('expires_at', '>=', now());
    }

    public function getExpiryStatusAttribute(): string
    {
        if (! $this->expires_at) return 'unknown';
        if ($this->expires_at->isPast()) return 'expired';
        if ($this->expires_at->diffInDays(now()) <= 30) return 'expiring';
        return 'active';
    }

    public function getSslExpiryStatusAttribute(): string
    {
        if (! $this->ssl_expires_at) return 'unknown';
        if ($this->ssl_expires_at->isPast()) return 'expired';
        if ($this->ssl_expires_at->diffInDays(now()) <= 30) return 'expiring';
        return 'active';
    }
}
