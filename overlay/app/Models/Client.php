<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\HasCustomFields;
use App\Models\Concerns\HasTags;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property string|null $type
 * @property bool $is_lead
 * @property string|null $website
 * @property string|null $referral
 * @property string|null $rate
 * @property string $currency_code
 * @property int $net_terms
 * @property string|null $tax_id_number
 * @property string|null $abbreviation
 * @property string|null $notes
 * @property bool $is_favorite
 * @property \Illuminate\Support\Carbon|null $accessed_at
 * @property \Illuminate\Support\Carbon|null $archived_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Contact> $contacts
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Location> $locations
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Asset> $assets
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Document> $documents
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Password> $passwords
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Domain> $domains
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Ticket> $tickets
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Deal> $deals
 * @property-read Contact|null $primaryContact
 * @property-read Location|null $primaryLocation
 */
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

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function passwords(): HasMany
    {
        return $this->hasMany(Password::class);
    }

    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    public function primaryContact(): HasOne
    {
        return $this->hasOne(Contact::class)->where('is_primary', true);
    }

    public function primaryLocation(): HasOne
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
