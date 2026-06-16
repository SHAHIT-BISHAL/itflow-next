<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
