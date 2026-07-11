<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutletProductionRunMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'outlet_production_run_id',
        'material_id',
        'product_id',
        'quantity_used',
    ];

    protected $casts = [
        'quantity_used' => 'decimal:2',
    ];

    public function productionRun(): BelongsTo
    {
        return $this->belongsTo(OutletProductionRun::class, 'outlet_production_run_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the display name for this consumed item (material or product).
     */
    public function getItemNameAttribute(): string
    {
        if ($this->material_id && $this->material) {
            return $this->material->name;
        }
        if ($this->product_id && $this->product) {
            return $this->product->name;
        }
        return 'Unknown Item';
    }

    /**
     * Get the SKU for this consumed item.
     */
    public function getItemSkuAttribute(): string
    {
        if ($this->material_id && $this->material) {
            return $this->material->sku;
        }
        if ($this->product_id && $this->product) {
            return $this->product->sku;
        }
        return '—';
    }

    /**
     * Get the unit for this consumed item.
     */
    public function getItemUnitAttribute(): string
    {
        if ($this->material_id && $this->material) {
            return $this->material->unit ?? 'units';
        }
        return 'units';
    }
}
