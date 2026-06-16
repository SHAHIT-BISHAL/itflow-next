<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'client_id', 'invoice_id',
        'amount', 'currency', 'method', 'reference', 'paid_at', 'notes',
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_at' => 'date',
    ];

    public function invoice() { return $this->belongsTo(Invoice::class); }
    public function client()  { return $this->belongsTo(Client::class); }
}
