<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'retail_price',
        'cost_price',
        'current_kitchen_stock',
    ];

    protected $casts = [
        'retail_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'current_kitchen_stock' => 'decimal:2',
    ];

    public function productionRuns(): HasMany
    {
        return $this->hasMany(ProductionRun::class);
    }

    public function outlets(): BelongsToMany
    {
        return $this->belongsToMany(Outlet::class, 'outlet_product')->withTimestamps();
    }

    public function showcaseRequestItems(): HasMany
    {
        return $this->hasMany(ShowcaseRequestItem::class);
    }

    public function outletStockMovements(): HasMany
    {
        return $this->hasMany(OutletStockMovement::class);
    }
}
