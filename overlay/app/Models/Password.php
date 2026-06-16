<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Crypt;

class Password extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id', 'client_id', 'name', 'username', 'password', 'url', 'notes', 'archived_at',
    ];

    protected $casts = [
        'archived_at' => 'datetime',
    ];

    // Never expose the raw encrypted value in serialisation
    protected $hidden = ['password'];

    public function setPasswordAttribute(?string $value): void
    {
        $this->attributes['password'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getDecryptedPasswordAttribute(): ?string
    {
        try {
            return $this->attributes['password'] ? Crypt::decryptString($this->attributes['password']) : null;
        } catch (\Exception) {
            return null;
        }
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function accessLogs(): HasMany
    {
        return $this->hasMany(PasswordAccessLog::class);
    }

    public function latestAccessLog(): HasOne
    {
        return $this->hasOne(PasswordAccessLog::class)->latestOfMany('accessed_at');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    public function scopeSearch($query, ?string $term)
    {
        return $query->when($term, fn ($q) => $q->where(function ($q2) use ($term) {
            $q2->where('name', 'like', "%{$term}%")
               ->orWhere('username', 'like', "%{$term}%")
               ->orWhere('url', 'like', "%{$term}%");
        }));
    }
}
