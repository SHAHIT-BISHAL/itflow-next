<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = ['company_id', 'name', 'color', 'icon'];

    public function clients()
    {
        return $this->morphedByMany(Client::class, 'taggable');
    }

    public function contacts()
    {
        return $this->morphedByMany(Contact::class, 'taggable');
    }
}
