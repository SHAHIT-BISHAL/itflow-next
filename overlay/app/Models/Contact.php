<?php

namespace App\Models;

use App\Models\Concerns\HasTags;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contact extends Model implements Authenticatable
{
    use HasFactory, HasTags, AuthenticatableTrait, Authorizable, Notifiable;

    protected $fillable = [
        'client_id', 'location_id', 'name', 'title', 'department', 'email', 'phone',
        'phone_extension', 'mobile', 'photo', 'is_primary', 'is_important',
        'is_billing', 'is_technical', 'notes',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_important' => 'boolean',
        'is_billing' => 'boolean',
        'is_technical' => 'boolean',
        'email_verified_at' => 'datetime',
        'accessed_at' => 'datetime',
        'archived_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function location(): BelongsTo
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
                ->orWhere('email', 'like', "%{$term}%");
        }));
    }
}
