<?php

namespace App\Models;

use App\Models\Concerns\HasTags;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $client_id
 * @property string $name
 * @property string|null $description
 * @property string|null $address
 * @property string|null $city
 * @property string|null $state
 * @property string|null $zip
 * @property string|null $country
 * @property string|null $phone
 * @property string|null $phone_extension
 * @property string|null $hours
 * @property bool $is_primary
 * @property bool $is_favorite
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $accessed_at
 * @property \Illuminate\Support\Carbon|null $archived_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Client $client
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Contact> $contacts
 */
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
