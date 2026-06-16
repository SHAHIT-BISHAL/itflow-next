<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class CustomField extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'model', 'label', 'type', 'options', 'sort_order'];

    protected $casts = [
        'options' => 'array',
    ];

    public function values()
    {
        return $this->hasMany(CustomFieldValue::class);
    }

    public function scopeForModel($query, string $modelClass)
    {
        return $query->where('model', $modelClass)->orderBy('sort_order');
    }
}
