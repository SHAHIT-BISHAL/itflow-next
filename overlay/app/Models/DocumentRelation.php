<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DocumentRelation extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id', 'related_type', 'related_id', 'relationship_type', 'notes',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function related(): MorphTo
    {
        return $this->morphTo();
    }
}
