<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FranchiseInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'sales_log_id',
        'outlet_id',
        'amount',
        'status', // unpaid, partially_paid, paid
        'due_date',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function salesLog(): BelongsTo
    {
        return $this->belongsTo(SalesLog::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(FranchiseReceipt::class);
    }

    public function getPaidAmountAttribute(): float
    {
        return (float) $this->receipts()->sum('amount');
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0.0, (float) $this->amount - $this->paid_amount);
    }
}
