<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $company_id
 * @property int|null $parent_id
 * @property string $name
 * @property string|null $description
 * @property string $type
 * @property string|null $color
 * @property string|null $icon
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $archived_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Category|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Category> $children
 */
class Category extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id', 'parent_id', 'name', 'description', 'type', 'color', 'icon', 'sort_order',
    ];

    protected $casts = [
        'archived_at' => 'datetime',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
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
