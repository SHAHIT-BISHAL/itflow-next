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
        'title', 'document_type', 'content', 'is_template',
        'review_due_at', 'reviewed_at', 'reviewed_by', 'archived_at',
    ];

    protected $casts = [
        'is_template' => 'boolean',
        'review_due_at' => 'date',
        'reviewed_at' => 'datetime',
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

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function versions()
    {
        return $this->hasMany(DocumentVersion::class)->orderByDesc('version_number');
    }

    public function relations()
    {
        return $this->hasMany(DocumentRelation::class);
    }

    public function latestVersion()
    {
        return $this->hasOne(DocumentVersion::class)->latestOfMany('version_number');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    public function scopeSearch($query, ?string $term)
    {
        return $query->when($term, fn ($q) => $q->where(function ($q2) use ($term) {
            $q2->where('title', 'like', "%{$term}%")
                ->orWhere('content', 'like', "%{$term}%");
        }));
    }

    public function scopeNeedsReview($query)
    {
        return $query->whereNotNull('review_due_at')
            ->whereDate('review_due_at', '<=', now()->toDateString());
    }

    public function getReviewStatusAttribute(): string
    {
        if (! $this->review_due_at) {
            return 'unscheduled';
        }

        if ($this->review_due_at->isPast() || $this->review_due_at->isToday()) {
            return 'due';
        }

        return $this->review_due_at->diffInDays(now()) <= 30 ? 'upcoming' : 'current';
    }
}
