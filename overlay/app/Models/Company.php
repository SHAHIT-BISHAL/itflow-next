<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name', 'address', 'city', 'state', 'zip', 'country',
        'phone', 'email', 'website', 'logo', 'locale', 'currency', 'timezone',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }
}
