<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\HasCustomFields;
use App\Models\Concerns\HasTags;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory, BelongsToCompany, HasTags, HasCustomFields;

    protected $fillable = [
        'company_id', 'name', 'type', 'is_lead', 'website', 'referral',
        'rate', 'currency_code', 'net_terms', 'tax_id_number', 'abbreviation',
        'notes', 'is_favorite', 'archived_at',
    ];

    protected $casts = [
        'is_lead' => 'boolean',
        'is_favorite' => 'boolean',
        'rate' => 'decimal:2',
        'accessed_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function passwords()
    {
        return $this->hasMany(Password::class);
    }

    public function domains()
    {
        return $this->hasMany(Domain::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function primaryContact()
    {
        return $this->hasOne(Contact::class)->where('is_primary', true);
    }

    public function primaryLocation()
    {
        return $this->hasOne(Location::class)->where('is_primary', true);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    public function scopeSearch($query, ?string $term)
    {
        return $query->when($term, fn ($q) => $q->where('name', 'like', "%{$term}%"));
    }
}
