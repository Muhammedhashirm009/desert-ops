<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutletStockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'outlet_id',
        'product_id',
        'material_id',
        'outlet_catalog_item_id',
        'from_location',
        'to_location',
        'quantity',
        'logged_by',
        'reference',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function catalogItem(): BelongsTo
    {
        return $this->belongsTo(OutletCatalogItem::class, 'outlet_catalog_item_id');
    }
}
