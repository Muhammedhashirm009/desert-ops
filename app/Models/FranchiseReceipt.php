<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FranchiseReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_number',
        'franchise_invoice_id',
        'amount',
        'receipt_date',
        'payment_method',
        'reference',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'receipt_date' => 'date',
    ];

    public function franchiseInvoice(): BelongsTo
    {
        return $this->belongsTo(FranchiseInvoice::class);
    }
}
