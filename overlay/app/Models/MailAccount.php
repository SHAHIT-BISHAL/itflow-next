<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class MailAccount extends Model
{
    use BelongsToCompany;

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
