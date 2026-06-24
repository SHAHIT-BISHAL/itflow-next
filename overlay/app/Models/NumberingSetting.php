<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $company_id
 * @property string $type
 * @property string $prefix
 * @property int $next_number
 * @property int $padding
 * @property string|null $suffix
 * @property-read Company $company
 */
class NumberingSetting extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'type',
        'prefix',
        'next_number',
        'padding',
        'suffix',
    ];

    protected $casts = [
        'next_number' => 'integer',
        'padding' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function format(int $number): string
    {
        return $this->prefix
            . str_pad((string) $number, $this->padding, '0', STR_PAD_LEFT)
            . ($this->suffix ?? '');
    }

    public function preview(): string
    {
        return $this->format($this->next_number);
    }
}
