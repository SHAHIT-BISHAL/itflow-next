<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentRelation extends Model
{
    protected $fillable = [
        'document_id', 'related_type', 'related_id', 'relationship_type', 'notes',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function related()
    {
        return $this->morphTo();
    }
}
