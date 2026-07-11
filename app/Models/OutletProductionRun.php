<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OutletProductionRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'outlet_id',
        'run_number',
        'product_id',
        'outlet_catalog_item_id',
        'quantity_produced',
        'prepared_date',
        'status',
        'destination',
        'qty_to_store',
        'qty_to_showcase',
        'prepared_by',
        'notes',
    ];

    protected $casts = [
        'prepared_date' => 'date',
        'quantity_produced' => 'decimal:2',
        'qty_to_store' => 'decimal:2',
        'qty_to_showcase' => 'decimal:2',
    ];

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function catalogItem(): BelongsTo
    {
        return $this->belongsTo(OutletCatalogItem::class, 'outlet_catalog_item_id');
    }

    public function getOutputNameAttribute(): string
    {
        if ($this->outlet_catalog_item_id && $this->catalogItem) {
            return $this->catalogItem->name;
        }
        if ($this->product_id && $this->product) {
            return $this->product->name;
        }
        return 'Unknown';
    }

    public function materials(): HasMany
    {
        return $this->hasMany(OutletProductionRunMaterial::class);
    }

    /**
     * Generate a unique run number for outlet production.
     */
    public static function generateRunNumber(): string
    {
        $year = date('Y');
        $lastRun = self::where('run_number', 'like', "OPR-{$year}-%")
            ->orderBy('run_number', 'desc')
            ->first();

        if ($lastRun) {
            $lastSeq = (int) substr($lastRun->run_number, -4);
            $nextSeq = str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextSeq = '0001';
        }

        return "OPR-{$year}-{$nextSeq}";
    }
}
