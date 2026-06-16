<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $company_id
 * @property int|null $client_id
 * @property int|null $password_id
 * @property int|null $user_id
 * @property string $action
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Illuminate\Support\Carbon $accessed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Client|null $client
 * @property-read Password|null $password
 * @property-read User|null $user
 */
class PasswordAccessLog extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id', 'client_id', 'password_id', 'user_id', 'action',
        'ip_address', 'user_agent', 'accessed_at',
    ];

    protected $casts = [
        'accessed_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function password(): BelongsTo
    {
        return $this->belongsTo(Password::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
