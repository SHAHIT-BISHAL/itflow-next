<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $company_id
 * @property int $client_id
 * @property int|null $contact_id
 * @property string $invoice_number
 * @property string $status
 * @property string $subtotal
 * @property string $tax_amount
 * @property string $total
 * @property string $amount_paid
 * @property string $currency
 * @property \Illuminate\Support\Carbon $issue_date
 * @property \Illuminate\Support\Carbon $due_date
 * @property string|null $notes
 * @property string|null $terms
 * @property \Illuminate\Support\Carbon|null $sent_at
 * @property \Illuminate\Support\Carbon|null $paid_at
 * @property \Illuminate\Support\Carbon|null $archived_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Client $client
 * @property-read Contact|null $contact
 * @property-read \Illuminate\Database\Eloquent\Collection<int, InvoiceItem> $items
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Payment> $payments
 * @property-read float $amount_due
 * @property-read string $status_color
 */
class Invoice extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id', 'client_id', 'contact_id', 'invoice_number', 'status',
        'subtotal', 'tax_amount', 'total', 'amount_paid', 'currency',
        'issue_date', 'due_date', 'notes', 'terms', 'sent_at', 'paid_at', 'archived_at',
    ];

    protected $casts = [
        'subtotal'    => 'decimal:2',
        'tax_amount'  => 'decimal:2',
        'total'       => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'issue_date'  => 'date',
        'due_date'    => 'date',
        'sent_at'     => 'datetime',
        'paid_at'     => 'datetime',
        'archived_at' => 'datetime',
    ];

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function contact(): BelongsTo { return $this->belongsTo(Contact::class); }
    public function items(): HasMany { return $this->hasMany(InvoiceItem::class)->orderBy('sort_order'); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }

    public function scopeActive($query)  { return $query->whereNull('archived_at'); }
    public function scopeOverdue($query) {
        return $query->whereNotIn('status', ['paid', 'void'])->where('due_date', '<', today());
    }

    public function getAmountDueAttribute(): float
    {
        return max(0, $this->total - $this->amount_paid);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'paid'    => 'green',
            'partial' => 'blue',
            'sent'    => 'yellow',
            'overdue' => 'red',
            'void'    => 'gray',
            default   => 'gray', // draft
        };
    }

    public function recalculate(): void
    {
        $subtotal   = $this->items->sum('amount');
        $taxAmount  = $this->items->sum(fn ($i) => $i->amount * $i->tax_rate / 100);
        $total      = $subtotal + $taxAmount;
        $amountPaid = $this->payments()->sum('amount');

        $status = $this->status;
        if ($this->status !== 'void') {
            if ($amountPaid >= $total && $total > 0) $status = 'paid';
            elseif ($amountPaid > 0) $status = 'partial';
            elseif ($this->due_date < today() && $this->status !== 'draft') $status = 'overdue';
        }

        $this->update([
            'subtotal'    => $subtotal,
            'tax_amount'  => $taxAmount,
            'total'       => $total,
            'amount_paid' => $amountPaid,
            'status'      => $status,
            'paid_at'     => $status === 'paid' ? ($this->paid_at ?? now()) : null,
        ]);
    }

    // Generate next invoice number for a company
    public static function nextNumber(int $companyId): string
    {
        $last = static::where('company_id', $companyId)->max('invoice_number');
        if (! $last) return 'INV-0001';
        preg_match('/(\d+)$/', $last, $m);
        return 'INV-' . str_pad((intval($m[1] ?? 0) + 1), 4, '0', STR_PAD_LEFT);
    }
}
