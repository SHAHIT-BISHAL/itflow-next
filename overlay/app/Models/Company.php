<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $name
 * @property string|null $address
 * @property string|null $city
 * @property string|null $state
 * @property string|null $zip
 * @property string|null $country
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $website
 * @property string|null $logo
 * @property string $locale
 * @property string $currency
 * @property string $timezone
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $users
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Client> $clients
 * @property-read CompanySetting|null $settings
 * @property-read \Illuminate\Database\Eloquent\Collection<int, NumberingSetting> $numberingSettings
 */
class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'address', 'city', 'state', 'zip', 'country',
        'phone', 'email', 'website', 'logo', 'locale', 'currency', 'timezone',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function settings(): HasOne
    {
        return $this->hasOne(CompanySetting::class);
    }

    public function numberingSettings(): HasMany
    {
        return $this->hasMany(NumberingSetting::class);
    }
}
