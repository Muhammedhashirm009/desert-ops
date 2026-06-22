<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'run_number',
        'product_id',
        'quantity_produced',
        'prepared_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'prepared_date' => 'date',
        'quantity_produced' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(ProductionRunMaterial::class);
    }
}
