<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property int|null $company_id
 * @property string $name
 * @property string $email
 * @property string|null $avatar
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon|null $archived_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Company|null $company
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Client> $permittedClients
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'company_id', 'name', 'email', 'password', 'avatar', 'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'archived_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Client IDs this user is restricted to. Empty = unrestricted (all clients in company).
     */
    public function permittedClients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'user_client_permissions');
    }

    public function hasClientRestrictions(): bool
    {
        return $this->permittedClients()->exists();
    }

    public function canAccessClient(Client $client): bool
    {
        if (! $this->hasClientRestrictions()) {
            return true;
        }

        return $this->permittedClients()->where('clients.id', $client->id)->exists();
    }

    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }
}
