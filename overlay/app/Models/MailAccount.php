<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property string $host
 * @property int $port
 * @property string $encryption
 * @property string $username
 * @property string|null $password
 * @property string $mailbox
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $last_polled_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string|null $decrypted_password
 */
class MailAccount extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id', 'name', 'host', 'port', 'encryption',
        'username', 'password', 'mailbox', 'is_active', 'last_polled_at',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'is_active'      => 'boolean',
        'last_polled_at' => 'datetime',
    ];

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
}
