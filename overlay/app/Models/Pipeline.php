<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property bool $is_default
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PipelineStage> $stages
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Deal> $deals
 */
class Pipeline extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = ['company_id', 'name', 'is_default', 'sort_order'];

    protected $casts = ['is_default' => 'boolean'];

    public function stages(): HasMany { return $this->hasMany(PipelineStage::class)->orderBy('sort_order'); }
    public function deals(): HasMany { return $this->hasMany(Deal::class); }
}
