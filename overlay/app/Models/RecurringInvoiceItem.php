<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $recurring_invoice_id
 * @property string $description
 * @property string $quantity
 * @property string $unit_price
 * @property string $tax_rate
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read RecurringInvoice $recurringInvoice
 */
class RecurringInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = ['recurring_invoice_id', 'description', 'quantity', 'unit_price', 'tax_rate', 'sort_order'];

    protected $casts = [
        'quantity'   => 'decimal:2',
        'unit_price' => 'decimal:2',
        'tax_rate'   => 'decimal:2',
    ];

    public function recurringInvoice(): BelongsTo { return $this->belongsTo(RecurringInvoice::class); }
}
