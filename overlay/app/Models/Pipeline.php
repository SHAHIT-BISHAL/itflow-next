<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pipeline extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = ['company_id', 'name', 'is_default', 'sort_order'];

    protected $casts = ['is_default' => 'boolean'];

    public function stages(): HasMany { return $this->hasMany(PipelineStage::class)->orderBy('sort_order'); }
    public function deals(): HasMany { return $this->hasMany(Deal::class); }
}
