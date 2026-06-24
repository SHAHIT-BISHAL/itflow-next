<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $document_id
 * @property int|null $created_by
 * @property int $version_number
 * @property string $title
 * @property string|null $content
 * @property string|null $change_summary
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Document $document
 * @property-read User|null $createdBy
 */
class DocumentVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id', 'created_by', 'version_number', 'title', 'content', 'change_summary',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
