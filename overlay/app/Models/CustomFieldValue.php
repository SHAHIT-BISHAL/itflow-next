<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomFieldValue extends Model
{
    use HasFactory;

    protected $fillable = ['custom_field_id', 'value'];

    public function customField()
    {
        return $this->belongsTo(CustomField::class);
    }

    public function customizable()
    {
        return $this->morphTo();
    }
}
