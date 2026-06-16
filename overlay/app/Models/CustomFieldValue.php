<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int $custom_field_id
 * @property string $customizable_type
 * @property int $customizable_id
 * @property string|null $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read CustomField $customField
 * @property-read Model $customizable
 */
class CustomFieldValue extends Model
{
    use HasFactory;

    protected $fillable = ['custom_field_id', 'value'];

    public function customField(): BelongsTo
    {
        return $this->belongsTo(CustomField::class);
    }

    public function customizable(): MorphTo
    {
        return $this->morphTo();
    }
}
