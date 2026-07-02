<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShowcaseRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'showcase_request_id',
        'product_id',
        'quantity_requested',
        'quantity_released',
        'release_source',
    ];

    protected $casts = [
        'quantity_requested' => 'decimal:2',
        'quantity_released' => 'decimal:2',
    ];

    public function showcaseRequest(): BelongsTo
    {
        return $this->belongsTo(ShowcaseRequest::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
