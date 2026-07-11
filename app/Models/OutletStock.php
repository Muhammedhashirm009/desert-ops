<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutletStock extends Model
{
    use HasFactory;

    protected $table = 'outlet_stocks';

    protected $fillable = [
        'outlet_id',
        'product_id',
        'material_id',
        'outlet_catalog_item_id',
        'quantity',
        'store_quantity',
        'kitchen_quantity',
        'showcase_quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'store_quantity' => 'decimal:2',
        'kitchen_quantity' => 'decimal:2',
        'showcase_quantity' => 'decimal:2',
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
