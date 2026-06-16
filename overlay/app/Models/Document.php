<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\HasTags;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory, BelongsToCompany, HasTags;

    protected $fillable = [
        'company_id', 'client_id', 'category_id', 'created_by',
        'title', 'content', 'is_template', 'archived_at',
    ];

    protected $casts = [
        'is_template' => 'boolean',
        'archived_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    public function scopeSearch($query, ?string $term)
    {
        return $query->when($term, fn ($q) => $q->where('title', 'like', "%{$term}%"));
    }
}
