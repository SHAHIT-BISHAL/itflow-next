<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'parent_id', 'name', 'description', 'type', 'color', 'icon', 'sort_order',
    ];

    protected $casts = [
        'archived_at' => 'datetime',
    ];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }
}
