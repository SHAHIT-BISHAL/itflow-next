<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $company_id
 * @property int|null $user_id
 * @property int|null $client_id
 * @property string $category
 * @property string $description
 * @property string $amount
 * @property string $currency
 * @property string|null $vendor
 * @property \Illuminate\Support\Carbon $expense_date
 * @property bool $is_billable
 * @property \Illuminate\Support\Carbon|null $invoiced_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User|null $user
 * @property-read Client|null $client
 */
class Expense extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id', 'user_id', 'client_id', 'category', 'description',
        'amount', 'currency', 'vendor', 'expense_date', 'is_billable', 'invoiced_at',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'expense_date' => 'date',
        'is_billable'  => 'boolean',
        'invoiced_at'  => 'datetime',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
}
