<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PipelineStage extends Model
{
    protected $fillable = ['pipeline_id', 'name', 'color', 'probability', 'sort_order'];

    public function pipeline() { return $this->belongsTo(Pipeline::class); }
    public function deals()    { return $this->hasMany(Deal::class, 'stage_id'); }
}
