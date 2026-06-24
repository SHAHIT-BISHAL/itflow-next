<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $company_id
 * @property int|null $ticket_id
 * @property int $user_id
 * @property int|null $client_id
 * @property string $description
 * @property int $minutes
 * @property \Illuminate\Support\Carbon $performed_at
 * @property bool $is_billable
 * @property string|null $rate
 * @property int|null $invoice_id
 * @property \Illuminate\Support\Carbon|null $invoiced_at
 * @property-read Ticket|null $ticket
 * @property-read User $user
 * @property-read Client|null $client
 * @property-read Invoice|null $invoice
 * @property-read float $hours
 * @property-read string $formatted_duration
 * @property-read float $amount
 */
class TimeEntry extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id', 'ticket_id', 'user_id', 'client_id',
        'description', 'minutes', 'performed_at', 'is_billable',
        'rate', 'invoice_id', 'invoiced_at',
    ];

    protected $casts = [
        'performed_at' => 'date',
        'is_billable'  => 'boolean',
        'minutes'      => 'integer',
        'rate'         => 'decimal:2',
        'invoiced_at'  => 'datetime',
    ];

    public function ticket(): BelongsTo  { return $this->belongsTo(Ticket::class); }
    public function user(): BelongsTo    { return $this->belongsTo(User::class); }
    public function client(): BelongsTo  { return $this->belongsTo(Client::class); }
    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }

    public function scopeBillable($query)    { return $query->where('is_billable', true); }
    public function scopeUninvoiced($query)  { return $query->whereNull('invoice_id'); }

    public function getHoursAttribute(): float
    {
        return round($this->minutes / 60, 2);
    }

    public function getFormattedDurationAttribute(): string
    {
        $h = intdiv($this->minutes, 60);
        $m = $this->minutes % 60;

        return $h > 0 ? "{$h}h {$m}m" : "{$m}m";
    }

    public function getAmountAttribute(): float
    {
        return round($this->hours * (float) ($this->rate ?? 0), 2);
    }
}
