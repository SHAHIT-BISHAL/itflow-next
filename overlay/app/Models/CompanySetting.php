<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $company_id
 * @property string $timezone
 * @property string $default_currency
 * @property string $tax_rate
 * @property int $default_net_terms
 * @property int $ticket_sla_hours
 * @property array<string, mixed>|null $business_hours
 * @property string|null $email_from_name
 * @property string|null $email_from_address
 * @property string|null $portal_name
 * @property string|null $portal_url
 * @property-read Company $company
 */
class CompanySetting extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'timezone',
        'default_currency',
        'tax_rate',
        'default_net_terms',
        'ticket_sla_hours',
        'business_hours',
        'email_from_name',
        'email_from_address',
        'portal_name',
        'portal_url',
    ];

    protected $casts = [
        'tax_rate' => 'decimal:2',
        'business_hours' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
