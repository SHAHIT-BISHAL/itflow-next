<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class PasswordAccessLog extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'client_id', 'password_id', 'user_id', 'action',
        'ip_address', 'user_agent', 'accessed_at',
    ];

    protected $casts = [
        'accessed_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function password()
    {
        return $this->belongsTo(Password::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
