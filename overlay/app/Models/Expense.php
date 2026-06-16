<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function user()   { return $this->belongsTo(User::class); }
    public function client() { return $this->belongsTo(Client::class); }
}
