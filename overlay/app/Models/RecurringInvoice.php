<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class RecurringInvoice extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'client_id', 'frequency', 'next_run_at', 'last_run_at',
        'is_active', 'notes', 'currency', 'net_terms',
    ];

    protected $casts = [
        'next_run_at' => 'date',
        'last_run_at' => 'date',
        'is_active'   => 'boolean',
    ];

    public function client() { return $this->belongsTo(Client::class); }
    public function items()  { return $this->hasMany(RecurringInvoiceItem::class)->orderBy('sort_order'); }

    public function generateInvoice(): Invoice
    {
        $invoice = Invoice::create([
            'company_id'     => $this->company_id,
            'client_id'      => $this->client_id,
            'invoice_number' => Invoice::nextNumber($this->company_id),
            'status'         => 'draft',
            'currency'       => $this->currency,
            'issue_date'     => today(),
            'due_date'       => today()->addDays($this->net_terms),
            'notes'          => $this->notes,
        ]);

        foreach ($this->items as $item) {
            $amount = $item->quantity * $item->unit_price;
            $invoice->items()->create([
                'description' => $item->description,
                'quantity'    => $item->quantity,
                'unit_price'  => $item->unit_price,
                'tax_rate'    => $item->tax_rate,
                'amount'      => $amount,
                'sort_order'  => $item->sort_order,
            ]);
        }

        $invoice->recalculate();

        $this->update([
            'last_run_at' => today(),
            'next_run_at' => $this->nextRunDate(),
        ]);

        return $invoice;
    }

    public function nextRunDate(): \Carbon\Carbon
    {
        return match ($this->frequency) {
            'weekly'    => today()->addWeek(),
            'quarterly' => today()->addMonths(3),
            'annually'  => today()->addYear(),
            default     => today()->addMonth(), // monthly
        };
    }
}
