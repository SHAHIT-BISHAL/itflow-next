<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int $document_id
 * @property string $related_type
 * @property int $related_id
 * @property string $relationship_type
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Document $document
 * @property-read Model $related
 */
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
