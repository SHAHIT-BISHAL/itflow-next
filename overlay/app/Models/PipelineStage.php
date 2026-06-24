<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $pipeline_id
 * @property string $name
 * @property string $color
 * @property int $probability
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Pipeline $pipeline
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Deal> $deals
 */
class PipelineStage extends Model
{
    use HasFactory;

    protected $fillable = ['pipeline_id', 'name', 'color', 'probability', 'sort_order'];

    public function pipeline(): BelongsTo { return $this->belongsTo(Pipeline::class); }
    public function deals(): HasMany { return $this->hasMany(Deal::class, 'stage_id'); }
}
