<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'category',
        'unit',
        'current_stock',
        'kitchen_stock',
        'min_stock_alert',
        'per_box_qty',
        'retail_price',
        'cost_price',
    ];

    protected $casts = [
        'retail_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
    ];

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function grnItems(): HasMany
    {
        return $this->hasMany(GoodsReceivedNoteItem::class);
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'material_supplier')
                    ->withPivot('unit_price', 'is_preferred', 'notes')
                    ->withTimestamps();
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('current_stock', '<=', 'min_stock_alert');
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->current_stock <= $this->min_stock_alert;
    }

    public function outletStockMovements(): HasMany
    {
        return $this->hasMany(OutletStockMovement::class);
    }
}
